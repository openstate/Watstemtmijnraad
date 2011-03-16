<?php

require_once('GettextPOGlobal.class.php');
require_once('RaadsstukTag.class.php');
require_once('Vote.class.php');
require_once('Politician.class.php');
require_once('LocalParty.class.php');
require_once('VoteMessage.class.php');
require_once('VoteException.class.php');
require_once('HNSSyncedRecord.class.php');

class Raadsstuk extends HNSSyncedRecord {
	private static $pofile;

	const NOTVOTED = 0;
	const ACCEPTED = 1;
	const REJECTED = 2;

	protected $data = array(
		'region' => null,
		'region_name' => null,
		'level_name' => null,
		'title' => null,
		'vote_date' => null,
		'summary' => null,
		'code' => null,
		'type' => null,
		'type_name' => null,
		'result' => null,
		'submitter' => null,
		'submit_type_name' => null,
		'parent' => null,
		'show' => null,
		'site_id' => null,
		'site' => null,
		'metainfo' => null,
		'vote_0' => null,
		'vote_1' => null,
		'vote_2' => null,
		'vote_3' => null,
		'vote_message' => null,
		'vote_exception' => null,
        'consensus' => null,
        'party' => null,
        'party_name' => null,
        'ext_url_info' => null,
	);

	protected $extraCols = array(
		'region_name' => 'r.name',
		'level_name' => 'l.name',
		'type_name' => 'rt.name',
		'submit_type_name' => 'st.name',
		'site' => 'ss.title',
		'vote_0' => 'v.vote_0',
		'vote_1' => 'v.vote_1',
		'vote_2' => 'v.vote_2',
		'vote_3' => 'v.vote_3',
		'party_name' => 'p.name',
	);

	protected $tableName = 'rs_raadsstukken';
	protected $multiTables = 'rs_raadsstukken t JOIN sys_site ss ON ss.id = t.site_id JOIN sys_regions r ON r.id = t.region JOIN sys_levels l ON r.level = l.id JOIN rs_raadsstukken_type rt ON t.type = rt.id JOIN rs_raadsstukken_submit_type st ON t.submitter = st.id JOIN rs_vote_cache v ON t.id = v.id LEFT JOIN pol_parties p ON t.party = p.id';


	private function _filterSite() {
		$ids = array_keys(Dispatcher::sessionUser() ? Dispatcher::sessionUser()->listSiteIds(): User::listDefaultSiteIds());
		if(empty($ids)) $wher = ' AND FALSE';
		else $wher = ' AND t.site_id IN ('.implode(', ', $ids).')';
		return $wher;
	}

	public function getListByRegion($region, $order = '', $limit = '') {
		return $this->getList('', 'WHERE region = '.$region.' '.$this->_filterSite(), $order, $limit);
	}

	public function getListByRegionWithWhere($region, $join = '', $where = '', $order = '', $limit = '') {
		return $this->getList($join, 'WHERE region = '.$region.' '.$this->_filterSite().' '.$where, $order, $limit);
	}

	public function getCountByRegion($region, $where = '') {
		return $this->getCount('WHERE region = '.$region.' '.$this->_filterSite() . ' ' . $where);
	}

	public function getCountForLastMonthByRegion($region) {
		return $this->getCount('WHERE region = '.$region.' AND vote_date < now() AND vote_date > now()-interval \'1 month\' '.$this->_filterSite());
	}

	public function getCountByCategory($region) {
		return $this->db->query('select c.id as category, c.name as name, count(*) from rs_raadsstukken t join rs_raadsstukken_categories rc on t.id = rc.raadsstuk join sys_categories c on rc.category = c.id where '.($region ? 't.region=% and ' : '').'t.result > 0 '.$this->_filterSite().' group by c.id, c.name order by c.name', $region)->fetchAllRows();
	}

	public function getSubmitters() {
		require_once 'Submitter.class.php';

		$submitters = new Submitter();

		return $submitters->getSubmittersByRaadsstuk($this->id);
	}

	public function showVotes() {
		return strtotime($this->vote_date) <= time();
	}

	public function getVoters() {
		$voters = array();
		foreach ($this->db->query('SELECT pf.politician, po.first_name, po.last_name, pf.party FROM rs_raadsstukken r JOIN pol_party_regions pr ON r.region = pr.region JOIN pol_politician_functions pf ON pf.region = pr.region AND pf.party = pr.party JOIN pol_politicians po ON po.id = pf.politician WHERE r.vote_date BETWEEN pf.time_start AND pf.time_end AND r.id = %', $this->id)->fetchAllRows() as $voter) {
			$voters[$voter['politician']] = $voter;
		}
		return $voters;
	}

	public function hasResult() {
		return $this->result > 0;
	}

	public function getResultTitle() {
		return self::getResultName($this->result);
	}

	public static function getResultName($result) {
		if (!self::$pofile)
			self::$pofile = new GettextPOGlobal('raadsstuk.po');

		switch ($result) {
            case self::NOTVOTED: return self::$pofile->getMsgStr('raadsstuk.notvoted');
            case self::ACCEPTED: return self::$pofile->getMsgStr('raadsstuk.accepted');
            case self::REJECTED: return self::$pofile->getMsgStr('raadsstuk.rejected');
		}
	}

	public static function getResultArray() {
		$names = array();
		for ($i = 0; $i < 3; $i++) {
			$names[$i] = self::getResultName($i);
		}
		return $names;
	}

	public function getPartyMessages() {
		$obj = new VoteMessage();
		$list = $obj->getList('', $this->db->formatQuery('WHERE raadsstuk = %', $this->id));
		$result = array();
		foreach ($list as $item)
			$result[$item->party] = $item;
		return $result;
	}

	public function getPartyExceptions() {
		$obj = new VoteException();
		$list = $obj->getList('', $this->db->formatQuery('WHERE raadsstuk = %', $this->id));
		$result = array();
		foreach ($list as $item)
			$result[$item->party] = $item;
		return $result;
	}



//========================- Checked code -===========================

	/**
	 * Returns true if this raadsstuk belongs to current selected site.
	 * Usually you will forbid viewing the raadsstuk if this method returns false (cross-site references).
	 *
	 * @see User.listSiteIds()
	 * @return boolean true this raadsstuk belongs to current selected site, false othwerise
	 */
	public function isForeignSite() {
		$ids = Dispatcher::sessionUser() ? Dispatcher::sessionUser()->listSiteIds(): User::listDefaultSiteIds();
		return !isset($ids[$this->site_id]);
	}

	/**
	 * Fetch all politicians that have voted for this raadsstuk while
	 * being associated with given party.
	 *
	 * @throws Exception if given party hasn't voted for this raadsstuk
	 * @param Party|integer $party associated party
	 * @param $order ordering rules for Vote
	 * @return array of Vote
	 */
	public function listVotesOfParty($party, $order = '') {
		$pid = is_object($party)? $party->id: intval($party);

		$vote = new Vote();
		return $vote->getList('JOIN rs_raadsstukken r ON r.id = t.raadsstuk JOIN pol_politician_functions pf ON pf.region = r.region AND t.politician = pf.politician AND r.vote_date BETWEEN pf.time_start AND pf.time_end', $this->db->formatQuery('WHERE r.id = %i AND pf.party = %i', $this->id, $pid), $order);
	}

	/**
	 * List all parties that have voted for this raadsstuk.
	 *
	 */
	/*public function listVotingParties($order) {
		$pv = new PartyVoteCache();
		$cache = $pv->loadVotesList($this->id);

	}*/

	/**
	 * List $limit recent raadsstukken.
	 *
	 * @param integer $limit number of raadsstukken to fetch
	 * @return array list of Raadsstuk unique by region
	 */
	public function listRecent($limit, $region = null, $activeOnly = false) {
		$ids = implode(',', array_keys(Dispatcher::sessionUser() ? Dispatcher::sessionUser()->listSiteIds(): User::listDefaultSiteIds()));

		$join= '';
		$extraWhere = '';
		if($activeOnly){
			$join = 'JOIN rs_vote_cache rvc ON rvc.id = t.id';
			$extraWhere = 'AND (rvc.vote_0 + rvc.vote_1 + rvc.vote_2 + rvc.vote_3) > 0';
		}

		$result = array();
		if($region == null) {
			$regs = $this->db->query("SELECT MAX(vote_date) as vd, region FROM {$this->tableName} t JOIN sys_regions r ON r.id = t.region {$join} WHERE site_id IN ({$ids}) AND show=1 {$extraWhere} AND r.hidden = 0 GROUP BY region ORDER BY vd DESC LIMIT %i", $limit)->fetchAllRows();
			foreach ($regs as $dat) {
				$rad = reset($this->getList('', $this->db->formatQuery('WHERE show=1 AND vote_date = %s AND region = %i AND site_id IN ('.$ids.') AND r.hidden = 0', $dat['vd'], $dat['region']), '', 'LIMIT 1'));
				$result[$rad->id] = $rad;
			}
		} else {
			$result = $this->getList($join, $this->db->formatQuery("WHERE site_id IN ({$ids}) AND show=1 AND region = %i {$extraWhere} AND r.hidden = 0 ", _id($region)), 'ORDER BY vote_date DESC', $this->db->formatQuery('LIMIT %i', $limit));
		}

		return $result;
	}

	/**
	 * List $limit recent raadstukken in a specific regions and all subregions.
	 *
	 * @param integer $limit number of raadsstukken to fetch
	 * @param $region the parent region
	 * @return array list of Raadsstuk'ken
	 */
	public function listRecentByRegion($limit, $region, $category = null, $tag = null, $subregions = true) {
		require_once('Region.class.php');

		if($category) {
			$category = is_array($category)? array_map('intval', $category): array(intval($category));
			$catex = ' AND t.id IN (SELECT rc.raadsstuk FROM rs_raadsstukken_categories rc WHERE rc.category IN ('.implode(', ', $category).'))';
		} else $catex = '';

		if($tag) {
			$tag = is_array($tag)? array_map('intval', $tag): array(intval($tag));
			$tagex = ' AND t.id IN (SELECT rc.raadsstuk FROM rs_raadsstukken_tags rc WHERE rc.tag IN ('.implode(', ', $tag).'))';
		} else $tagex = '';

		$r = new Region();
		if($subregions){
			$regs = $r->selectSubtreeIDs($region);
		} else {
			$regs = array($region);
		}

        $notpre = !Dispatcher::inst()->preview;
		return (count($regs) < 1)? array():
								   $this->getList('', 'WHERE show = 1 '.($notpre? 'AND r.hidden = 0': '').' AND region IN ('.implode(', ', $regs).') '.$this->_filterSite()." {$catex} {$tagex}",
								                      'ORDER BY vote_date DESC, t.id DESC', $this->db->formatQuery('LIMIT %i', $limit));
	}

	/**
	 * Fetch raadsstukken of specific politician by vote, limiting result by region subtree and optionally category with tag.
	 *
	 * @param $limit max result length
	 * @param $politician politician id
	 * @param $region region id (root of the subtree)
	 * @param $category category id or list of ids
	 * @param $tag tag id or list of tag ids
	 * @return list of Raadsstuk objects
	 */
	public function listRecentByPoliticianAndRegion($limit, $politician, $region, $category = null, $tag = null) {
		$ids = implode(',', array_keys(Dispatcher::sessionUser() ? Dispatcher::sessionUser()->listSiteIds(): User::listDefaultSiteIds()));
		$r = new Region();
		$regs = $r->selectSubtreeIDs($region);

		if($category) {
			$category = is_array($category)? array_map('intval', $category): array(intval($category));
			$catex = ' AND t.id IN (SELECT rc.raadsstuk FROM rs_raadsstukken_categories rc WHERE rc.category IN ('.implode(', ', $category).'))';
		} else $catex = '';

		if($tag) {
			$tag = is_array($tag)? array_map('intval', $tag): array(intval($tag));
			$tagex = ' AND t.id IN (SELECT rc.raadsstuk FROM rs_raadsstukken_tags rc WHERE rc.tag IN ('.implode(', ', $tag).'))';
		} else $tagex = '';

		return $this->getList('JOIN rs_votes rv ON rv.raadsstuk = t.id',
		  $this->db->formatQuery('WHERE show=1 AND t.site_id IN ('.$ids.') AND rv.politician = %i AND t.region IN ('.implode(', ', $regs).') '.$catex.$tagex, $politician),
		  'ORDER BY vote_date DESC', $this->db->formatQuery('LIMIT %i', $limit),
		  'rv.vote as polvote'
		  );
	}


	/**
	 * List $limit recent raadsstukken of specific politician.
	 *
	 * Note: currently politician is linked by vote, however this may change in future.
	 *
	 * @param integer $limit number of raadsstukken to fetch
	 * @param integer $politician id of specific politician
	 * @return array list of Raadsstuk'ken
	 */
	public function listRecentByPolitician($limit, $politician) {
		//$ids = implode(',', array_keys(Dispatcher::sessionUser() ? Dispatcher::sessionUser()->listSiteIds(): User::listDefaultSiteIds()));
		//t.site_id IN ({$ids}) AND

		return $this->getList('JOIN rs_votes rv ON rv.raadsstuk = t.id',
		  $this->db->formatQuery("WHERE show = 1 AND rv.politician = %i", $politician),
		  'ORDER BY vote_date DESC', $this->db->formatQuery('LIMIT %i', $limit),
		  'rv.vote as polvote'
		  );
	}

	/**
	 * List $limit recent raadsstukken of specific party.
	 * Note: currently party is linked by vote.
	 *
	 * @param integer $limit number of raadsstukken to fetch
	 * @param integer $party id of specific party
	 * @param mixed limit search to specific region or regions
	 * @return array list of Raadsstuk'ken
	 */
	public function listRecentByParty($limit, $party, $region = null, $category = null, $tag = null) {
		$ids = implode(',', array_keys(Dispatcher::sessionUser() ? Dispatcher::sessionUser()->listSiteIds(): User::listDefaultSiteIds()));

		if($region === null) $regs = '';
		elseif(is_scalar($region)) $regs = $this->db->formatQuery('AND t.region = %i', $region);
		else {
			$region = array_map('intval', array_filter(array_map('trim', $region)));
			$regs = (sizeof($region) > 0)? 'AND t.region IN ('.implode(', ', $region).')': '';
		}

		if($category) {
			$category = is_array($category)? array_map('intval', $category): array(intval($category));
			$catex = ' AND t.id IN (SELECT rc.raadsstuk FROM rs_raadsstukken_categories rc WHERE rc.category IN ('.implode(', ', $category).'))';
		} else $catex = '';

		if($tag) {
			$tag = is_array($tag)? array_map('intval', $tag): array(intval($tag));
			$tagex = ' AND t.id IN (SELECT rc.raadsstuk FROM rs_raadsstukken_tags rc WHERE rc.tag IN ('.implode(', ', $tag).'))';
		} else $tagex = '';

		return $this->getList($this->db->formatQuery('JOIN rs_party_vote_cache pvc ON pvc.raadsstuk = t.id AND pvc.party = %i', $party),
		  $this->db->formatQuery("WHERE show = 1 AND t.id IN (SELECT rv.raadsstuk FROM rs_votes rv WHERE rv.party = %i) {$regs} {$catex} {$tagex} AND t.site_id IN ({$ids})", $party),
		  'ORDER BY vote_date DESC', $this->db->formatQuery('LIMIT %i', $limit),
		  'pvc.vote_0 as party_vote_0, pvc.vote_1 as party_vote_1, pvc.vote_2 as party_vote_2, pvc.vote_3 as party_vote_3'
		  );
	}

	/**
	 * List children raadsstukken of specific type.
	 *
	 * @param integer $type RaadsstukType::* constant or null to select all children
	 * @return array of Raadsstuk
	 */
	public function listChildren($type = null) {
		return $this->getList('', $this->db->formatQuery('WHERE t.parent = %i '.($type !== null? 'AND t.type = %i': ''), $this->id, $type));
	}

   /**
    * Get 'consensus' by id Integer/Array
    * @param $id Integer/Array
    * @return array
    */
    public function getConcensus($id) {
        if(is_array($id))
            $result = $this->getList('', $this->db->formatQuery('WHERE t.id IN ( %% )'), implode(',', $id));
        if(is_numeric($id))
            $result = $this->getList('', $this->db->formatQuery('WHERE t.id = %i'), $id);

		return $result;
    }


    ///////////////////////////////////////////////////////
	//                                                   //
	//     HNS.dev SYNCHRONISATION RELATED FUNCTIONS     //
	//                                                   //
	///////////////////////////////////////////////////////


    public function __get($name){
    	switch($name) {
    		case 'hns_title':
    			return mb_strimwidth($this->title, 0, 45);
    			break;
    		case 'hns_summary':
    			return mb_strimwidth($this->summary, 0, 250);
    			break;
    		case 'hns_code':
    			return mb_strimwidth($this->code, 0, 250);
    			break;
    		case 'hns_parent_id':
    			if(is_null($this->parent)) return null;

				$parentRS = new self($this->parent);

				if (!$parentRS->hasHnsId()) $parentRS->save();

				return $parentRS->hnsId();
    		case 'hns_org_id':
				if ($this->hns_party_id) return $this->hns_party_id;

				// Submit type name is not updated immediately after save/update
				$raadsstuk = new Raadsstuk($this->id);
				$submitTypeName = $raadsstuk->submit_type_name;

				if (array_key_exists($submitTypeName, $this->org_types)) {
					return $this->fetchHnsOrganizationId($submitTypeName);
				}
				return null;
			case 'hns_party_id':
				if (is_null($this->party)) return null;
				if (is_null($this->region)) return null;

				$localParty = LocalParty::loadLocalParty($this->party, $this->region);

				if (empty($localParty)) return null;

				if (!$localParty->hasHnsId()) $localParty->save();

				return $localParty->hnsId();
    		case 'hns_region_id':
    			if(is_null($this->region)) return null;

    			$rsRegion = new Region($this->region);

    			if (!$rsRegion->hasHnsId())$rsRegion->save();

    			return $rsRegion->hnsId();
			case 'hns_type_name':
				// type_name is not updated immediately after save/update
				$raadsstuk = new Raadsstuk($this->id);

				return $raadsstuk->type_name;
			case 'hns_result':
    			return ucwords($this->getResultName($this->result));
    		default:
    			return parent::__get($name);
    	}
    }

	public function verifyCanSyncToHns() {
		if (!$this->show) {
			throw new HnsCannotSyncError('Raadsstuk.show == false');
		}

        if ($this->parent) {
            $parent = new Raadsstuk($this->parent);

            $parent->verifyCanSyncToHns();
        }

		return parent::verifyCanSyncToHns();
	}

	protected function getHnsMapping() {
		$mapping = array(
			'hnsTable' => 'document',
			'fields'   => array(
				'hns_title'     => 'title',
				'hns_summary'   => 'summary',
				'vote_date'     => 'vote_date',
				'hns_type_name' => 'type',
				'hns_code'      => 'code',
				'hns_parent_id' => 'parent',
				'hns_org_id'    => 'submitter_organization',
				'hns_region_id' => 'region',
				'hns_result'    => 'result',
			),
		);

		return $mapping;
	}

	protected $uniques = array(
		'hns_title' => 'title',
		'vote_date' => 'vote_date',
		'hns_type_name' => 'type',
		'hns_code'  => 'code',
	);

	protected $org_types = array(
		'College' => 'Overheid',
		'Presidium' => 'Overheid',
		'Burger' => 'Burger'
	);

	protected $org_ids = array();

	protected function fetchHnsOrganizationId($orgName, $orgType = null) {
		if (isset($this->org_ids[$orgName])) return $this->org_ids[$orgName];

		if (is_null($orgType) && isset($this->org_types[$orgName])) {
			$orgType = $this->org_types[$orgName];
		}

		if (is_null($orgType)) {
			throw new HnsApiError("Cannot fetch organization '{$orgName}' with unknown type");
		}

		$query = "
			<query>
				<select>name</select>
				<from>organization</from>
				<where>name = '{$orgName}'</where>
				<where>type = '{$orgType}'</where>
 				<where>area.id = {$this->hns_region_id}</where>
			</query>
		";

		$result = $this->execute($query);

		if (isset($result['organization'][0]['id'])) {
			$this->org_ids[$orgName] = $orgId;

			return $result['organization'][0]['id'];
		}

		$orgId = $this->createOrganization($orgName);

		$this->org_ids[$orgName] = $orgId;

		return $orgId;
	}

	protected function createOrganization($orgName) {
		$query = "
			<insert>
				<organization>
					<name>{$orgName}</name>
					<type>{$this->org_types[$orgName]}</type>
					<area>{$this->hns_region_id}</area>
				</organization>
			</insert>
		";

		return parent::insertHnsEntry($query, 'organization');
	}
}

?>
