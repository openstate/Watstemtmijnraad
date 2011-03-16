<?php

require_once('DBs.class.php');
require_once('SearchResult.class.php');
require_once('Xml.class.php');
require_once('SearchParser.class.php');
require_once('Politician.class.php');
require_once('Party.class.php');
require_once('Region.class.php');
require_once('Appointment.class.php');

class SearchEngine {
	private $db;
	private $params; //formatted where expressions
	private $fts = false;
	private $hloptions = 'StartSel=[[, StopSel=]], MaxWords=30, MinWords=13';
	private $hlTitleoptions = 'StartSel=[[, StopSel=]], MaxWords=15, MinWords=7';
	private $voteCacheOption = self::VOTE_CACHE_ALL;
	private $voteCacheQuery = null;

	const MAX_RESULTS = 1000;

	const VOTE_CACHE_ALL = 0;
	const VOTE_CACHE_PARTY = 1;
	const VOTE_CACHE_POLITICIAN = 2;

	public static $fields = array('q', 'region', 'code', 'title', 'summary', 'category', 'type', 'vote_date', 'tags', 'party', 'politician_id', 'submitter_id');
	protected $inflection = array('region' => 't.region', 'title' => 't.title', 'category' => 'coalesce(rc.category, -1)', 'submitter_id' => '', 'politician_id' => '', 'party' => '', 'tags' => 'ta.name', 'submitter_id' => 'rs.politician');
	protected $transformations = array('fts', 'int', 'addPcBoth', 'addPcBoth', 'addPcBoth', 'int', 'int', 'addPcEnd', 'addPcBoth', 'ignore', 'ignore', 'int');
	protected $operators = array('', '=', 'ILIKE', 'ILIKE', 'ILIKE', '=', '=', 'LIKE', 'ILIKE', '', '', '=');
	protected $voteCacheOptions = array(self::VOTE_CACHE_ALL, self::VOTE_CACHE_ALL, self::VOTE_CACHE_ALL, self::VOTE_CACHE_ALL, self::VOTE_CACHE_ALL, self::VOTE_CACHE_ALL, self::VOTE_CACHE_ALL, self::VOTE_CACHE_ALL, self::VOTE_CACHE_ALL, self::VOTE_CACHE_PARTY, self::VOTE_CACHE_POLITICIAN, self::VOTE_CACHE_ALL);
	protected $explode = array('region', 'party', 'politician_id');

	public function __construct($params) {
		$this->db = DBs::inst(DBs::SYSTEM);
		foreach ($params as $key => $value) {
			if (($pos = array_search($key, self::$fields)) >= 0 && $value != null) { //[FIXME: 0 id's will be skipped!]
				$tr = $this->transformations[$pos];
				if ($this->voteCacheOptions[$pos] > $this->voteCacheOption) {
					$this->voteCacheOption = $this->voteCacheOptions[$pos];
					$this->voteCacheQuery = $value; //party or politician  id
				}
				if (in_array($key, $this->explode)) $this->$key = intval($value); //sometimes people pass here parameters as-is from the request, was the security hole
				$value = Transformation::$tr($value);
				if ('q' == $key) { // full text search overrides all
					$this->params['fts'] = $value;

					$this->fts = true;
					//return;
				} else if ($value) {
					//Exception:
					if($this->fts && $key == 'category'){
						$this->params['category'] = $value;
						continue;
					}

					$this->params[] = $this->db->formatQuery((isset($this->inflection[$key]) ? $this->inflection[$key] : $key).' '.$this->operators[$pos].' %', $value);
				}
			}
		}
	}

	public function getFilterInformation() {
		switch($this->voteCacheOption) {
			case self::VOTE_CACHE_ALL:
				return array();

			case self::VOTE_CACHE_PARTY:
				try {
					$party = new Party($this->party);
					$region = new Region(@$this->region);
					return array($party->name, $region->formatName());
				} catch (Exception $e) { //Party/Region record not found... broken link?
					return array(); //anyway the results should be empty
				}

			case self::VOTE_CACHE_POLITICIAN:
				try {
					$politician = new Politician($this->politician_id);
					$result = array($politician->formatName());
					$appointment = $politician->getLatestAppointment(@$this->region);

					if ($appointment) {
						$result[] = $appointment->getParty()->name;
						$result[] = $appointment->getRegion()->formatName();
						$result[] = 'In de gemeenteraad'. //[FIXME: non-localized string!]
							//[FIXME: Postrgres -infinity! Record must convert it to NULL for better portability]
							($appointment->time_start != '-infinity' ? ' van '.strftime2('%e %B %Y', strtotime($appointment->time_start)) : '').
							($appointment->time_end != 'infinity' ? ' tot '.strftime2('%e %B %Y', strtotime($appointment->time_end)) : ' tot heden');
					}

					return $result;
				} catch (Exception $e) { //one of the records is not found, broken link
					return array();
				}
		}
	}

	public function getVoteCacheOption() {
		return $this->voteCacheOption;
	}

	protected function getVoteCacheTable() {
		switch($this->voteCacheOption) {
			case self::VOTE_CACHE_ALL: return array(
				'select' => 'vc.*',
				'from' => 'rs_vote_cache vc USING (id)',
				'where' => 'TRUE'
			);
			case self::VOTE_CACHE_PARTY: return array(
				'select' => 'vc.vote_0, vc.vote_1, vc.vote_2, vc.vote_3',
				'from' => 'rs_party_vote_cache vc ON vc.raadsstuk = t.id',
				'where' => 'vc.party = '.(int) $this->voteCacheQuery
			);
			case self::VOTE_CACHE_POLITICIAN: return array(
				'select' => '
					CASE WHEN vc.vote = 0 THEN 1 ELSE 0 END AS vote_0,
					CASE WHEN vc.vote = 1 THEN 1 ELSE 0 END AS vote_1,
					CASE WHEN vc.vote = 2 THEN 1 ELSE 0 END AS vote_2,
					CASE WHEN vc.vote = 3 THEN 1 ELSE 0 END AS vote_3',
				'from' => 'rs_votes vc ON vc.raadsstuk = t.id',
				'where' => 'vc.politician = '.(int) $this->voteCacheQuery
			);
		}
	}

	public function getResults() {
		$vc = $this->getVoteCacheTable();

		//allowed site
		$ids = implode(', ', array_keys(Dispatcher::sessionUser() ? Dispatcher::sessionUser()->listSiteIds(): User::listDefaultSiteIds()));

		//[FIXME: this is one friggin slow query with lots of unused tables, lets hope this will be optimized by DB...]
		$sql = 'SELECT DISTINCT t.*';
		if ($this->fts)
			$sql .= ", headline(t.title, q, '".$this->hlTitleoptions."') as title_hl, headline(t.summary, q, '".$this->hloptions."') as summary_hl, rank(vector, q)";

		$sql .= ', '.$vc['select'].', r.name AS region_name, ty.name AS type_name FROM rs_raadsstukken t JOIN '.$vc['from'].' JOIN sys_regions r ON t.region = r.id JOIN rs_raadsstukken_type ty ON t.type = ty.id';
		//else {
		$sql .= ' LEFT JOIN rs_raadsstukken_tags rt ON rt.raadsstuk = t.id LEFT JOIN sys_tags ta ON rt.tag = ta.id LEFT JOIN rs_raadsstukken_categories rc ON rc.raadsstuk = t.id LEFT JOIN rs_raadsstukken_submitters rs ON rs.raadsstuk = t.id LEFT JOIN pol_politicians p ON p.id = rs.politician LEFT JOIN pol_politician_functions pf ON pf.politician = rs.politician LEFT JOIN pol_parties par ON pf.party = par.id';
		$this->params[] = $vc['where'];

		if($this->fts) {
			$sql .= ' JOIN rs_raadsstukken_vectors v ON t.id = v.id, to_tsquery('.$this->db->formatQuery('%', $this->params['fts']).') AS q';
			unset($this->params['fts']);
			$sql .= ' WHERE v.vector @@ q';
		}


		if (count($this->params)){
			$this->fts ? $sql .= ' AND ' : $sql .= ' WHERE ';

			if($this->fts && isset($this->params['category'])) {
				$tmp_category = $this->params['category'];
				unset($this->params['category']);
			}

			$sql .= implode(' AND ', $this->params);
		}
		if(isset($tmp_category)) {
			$sql .= ' OR coalesce(rc.category, -1) = '.$tmp_category;
		}

		$sql .= ' AND t.site_id IN ('.$ids.')';
		$sql .= ' AND t.vote_date BETWEEN COALESCE(pf.time_start, \'-infinity\'::timestamp without time zone) AND COALESCE(pf.time_end, \'infinity\'::timestamp without time zone) AND t.result > 0 ' . (!Dispatcher::inst()->preview? 'AND r.hidden = 0': '');
		$this->fts ? $sql .= ' ORDER BY rank, vote_date DESC, t.id DESC' : $sql .= ' ORDER BY vote_date DESC, t.id DESC';
		//}
		$sql .= ' LIMIT '.self::MAX_RESULTS;

		if ($this->fts)
			$this->db->query("SELECT set_curcfg('dutch')");
		$rows = $this->db->query($sql)->fetchAllRows();
		$result = array();

		if (count($rows) > 0) {
			$tags = $this->getTags($rows);
			$categories = $this->getCategories($rows);
			foreach ($rows as $row) {
				if (!isset($result[$row['id']]))
					$result[$row['id']] = new SearchResult($row, @$tags[$row['id']], @$categories[$row['id']]);
			}
		}
		return $result;
	}

	private function getTags($rows) {
		$ids = array();
		foreach ($rows as $row) {
			$ids[] = $row['id'];
		}
		$sql = 'SELECT rt.raadsstuk, t.id, t.name FROM rs_raadsstukken_tags rt JOIN sys_tags t ON rt.tag = t.id WHERE rt.raadsstuk IN ('.implode(',', $ids).')';
		$tags = $this->db->query($sql)->fetchAllRows();
		$t = array();
		foreach ($tags as $tag) {
			$t[$tag['raadsstuk']][] = array('id' => $tag['id'], 'name' => $tag['name']);
		}
		return $t;
	}

	private function getCategories($rows) {
		$ids = array();
		foreach ($rows as $row) {
			$ids[] = $row['id'];
		}
		$sql = 'SELECT rc.raadsstuk, c.id, c.name FROM rs_raadsstukken_categories rc JOIN sys_categories c ON rc.category = c.id WHERE rc.raadsstuk IN ('.implode(',', $ids).')';
		$categories = $this->db->query($sql)->fetchAllRows();
		$c = array();
		foreach ($categories as $category) {
			$c[$category['raadsstuk']][] = array('id' => $category['id'], 'name' => $category['name']);
		}
		return $c;
	}

	public function getResultsXml() {
		$xml = Xml::getDefault();
		$s = Xml::getPrelude().$xml->getRoot('raadsstukken');
		foreach ($this->getResults() as $r) {
			$s .= $r->toXml($xml);
		}
		return $s.$xml->getTag('raadsstukken', true);
	}

	public function isFts() {
		return $this->fts;
	}
}

class Transformation {
	public static function ignore($value) {
		return false;
	}

	public static function none($value) {
		return $value;
	}

	public static function addPcBoth($value) {
		return '%'.$value.'%';
	}

	public static function addPcEnd($value) {
		return $value.'%';
	}

	public static function fts($value) {
		$parser = new SearchParser($value);
	  return $parser->parse();
	}

	public static function int($value) {
		return intval($value);
	}
}

?>