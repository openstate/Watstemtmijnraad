<?php

require_once('Politician.class.php');
require_once('GettextPOGlobal.class.php');
require_once('HNSSyncedRecord.class.php');

class Vote extends HNSSyncedRecord {
	private static $pofile;

	protected $data = array(
		'politician' => null,
		'title' => null,
		'first_name' => null,
		'last_name' => null,
		'gender_is_male' => null,
		'raadsstuk' => null,
		'vote' => null,
		'party' => null,
		'message' => null,
		'exception' => null,
	);

	protected $extraCols = array(
		'title' => 'p.title',
		'first_name' => 'p.first_name',
		'last_name' => 'p.last_name',
		'gender_is_male' => 'p.gender_is_male',
	);

	protected $tableName = 'rs_votes';
	protected $multiTables = 'rs_votes t JOIN pol_politicians p ON t.politician = p.id';


	/** Returns (politician_id => Vote) */
	public function loadByRaadsstuk($raadsstuk) {
		$votes = array();
		foreach ($this->getList('', $this->db->formatQuery('WHERE raadsstuk = %i', _id($raadsstuk))) as $vote) {
			$votes[$vote->politician] = $vote;
		}
		return $votes;
	}

	/**
	 * Counts all votes
	 * @param $politician
	 * @return unknown_type
	 */
	public static function countVotes($politician, $region = null) {
		$v = new Vote();
		if($region) return $v->db->query('SELECT COUNT(*) FROM rs_votes v JOIN rs_raadsstukken r ON v.raadsstuk = r.id WHERE v.politician = %i AND r.region = %i', _id($politician), _id($region))->fetchCell();
		else return $v->db->query('SELECT COUNT(*) FROM rs_votes v JOIN rs_raadsstukken r ON v.raadsstuk = r.id WHERE v.politician = %i', _id($politician))->fetchCell();
	}

    public static function countVotesLastMonth($region_ids) {
        $v = new Vote();
        if($region_ids && is_array($region_ids)) return $v->db->query('SELECT r.region, COUNT(*) as count FROM rs_votes v JOIN rs_raadsstukken r ON v.raadsstuk = r.id WHERE r.region IN ('. implode(', ', $region_ids) . ') AND date_trunc(\'month\', v.vote_date) = date_trunc(\'month\', (now() - interval \'1 month\')) GROUP BY r.region')->fetchallRows();
    }

	/**
	 * Counts all pro or contra votes. Not the absent votes
	 * @param $politician
	 * @return unknown_type
	 */
	public static function countRealVotes($politician, $region = null) {
		$v = new Vote();
		if($region) return $v->db->query('SELECT COUNT(*) FROM rs_votes v JOIN rs_raadsstukken r ON v.raadsstuk = r.id WHERE v.politician = %i AND (v.vote = 0 OR v.vote = 1) AND r.region = %i', _id($politician), _id($region))->fetchCell();
		else return $v->db->query('SELECT COUNT(*) FROM rs_votes v  JOIN rs_raadsstukken r ON v.raadsstuk = r.id WHERE v.politician = %i AND (v.vote = 0 OR v.vote = 1)', _id($politician))->fetchCell();
	}

	public function countObsolete($politician, $exclude, $start = null, $end = null) {
		$v = new Vote();
		return $v->db->query('SELECT COUNT(*) FROM rs_votes v
									 WHERE v.politician = %i AND v.id NOT IN (
									     SELECT v.id
									     FROM rs_votes v
									     JOIN pol_politician_functions pf ON v.politician = pf.politician
									     JOIN rs_raadsstukken r ON r.id = v.raadsstuk

									     WHERE v.politician = %i
									     AND r.vote_date BETWEEN pf.time_start AND pf.time_end '.
										 ($start == null && $end == null? ' AND pf.id <> %i': " AND (pf.id <> %i OR r.vote_date BETWEEN %s AND %s)").
									 ')', _id($politician), _id($politician), _id($exclude), $start, $end)->fetchCell();
	}

	public function getByRaadsstukAndPolitician($raadsstuk, $politician) {
		$list = $this->getList('', $this->db->formatQuery('WHERE raadsstuk = %i AND politician = %i', _id($raadsstuk), _id($politician)));
		return array_shift($list);
	}

	public function formatName() {
		return Politician::formatPoliticianName($this->title, $this->first_name, $this->last_name, $this->gender_is_male);
	}

	public function getVoteTitle() {
		return self::getVoteTitleStatic($this->vote);
	}

	public static function getVoteTitleStatic($vote) {
		if (!self::$pofile)
			self::$pofile = new GettextPOGlobal('votes.po');

		switch ($vote) {
			case -1: return self::$pofile->getMsgStr('votes.verdeeld');
			case  0: return self::$pofile->getMsgStr('votes.voor');
			case  1: return self::$pofile->getMsgStr('votes.tegen');
			case  2: return self::$pofile->getMsgStr('votes.onthouden');
			case  3: return self::$pofile->getMsgStr('votes.afwezig');
		}
	}


    ///////////////////////////////////////////////////////
	//                                                   //
	//     HNS.dev SYNCHRONISATION RELATED FUNCTIONS     //
	//                                                   //
	///////////////////////////////////////////////////////


	public function __get($name){
		switch ($name){
			case 'hns_person_id':
				$votePolitician = new Politician($this->politician);
				if(!$votePolitician->hasHnsId()) {
					$votePolitician->save();
				}
				return $votePolitician->hnsId();
				break;
			case 'hns_document_id':
				$raadsstuk = new Raadsstuk($this->raadsstuk);

				if (!$raadsstuk->show) throw new HnsApiError('Vote should not be saved! Raadsstuk->show == false');

				if(!$raadsstuk->hasHnsId()) {
					$raadsstuk->save();
				}

				return $raadsstuk->hnsId();
				break;
			case 'hns_vote':
				return $this->getHnsVote($this->vote);
				break;
			default:
				return parent::__get($name);
		}
	}

	public function verifyCanSyncToHns() {
		$raadsstuk = new Raadsstuk($this->raadsstuk);

		if (!$raadsstuk->show) {
			throw new HnsCannotSyncError("Cannot sync Vote({$this->id}): Raadsstuk.show == false");
		}

		return parent::verifyCanSyncToHns();
	}

	protected function getHnsMapping() {
		$mapping = array(
			'hnsTable' => 'vote',
			'fields'   => array(
				'hns_person_id'   => 'person',
				'hns_document_id' => 'document',
				'hns_vote'        => 'vote',
			),
		);

		return $mapping;
	}

	protected $uniques = array(
		'hns_person_id'   => 'person',
		'hns_document_id' => 'document',
	);


	private function getHnsVote($vote = null){
		if(is_null($vote)){
			$vote = $this->vote;
		}

		switch($vote){
			case 0:
				return 'Voor';
				break;
			case 1:
				return 'Tegen';
				break;
			case 2:
				return 'Onthouding';
				break;
			case 3:
				return 'Afwezig';
				break;
		}
	}
}

?>
