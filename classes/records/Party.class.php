<?php
require_once('ObjectList.class.php');
require_once('Raadsstuk.class.php');

class Party extends Record {

	protected $data = array(
		'name' => null,
		'combination' => null,
		'owner' => null,
		'level' => null,
		'short_form' => null,
		'image' => null,
	);

	protected $extraCols = array(
		'level' => 'r.level'
	);

	protected $tableName = 'pol_parties';
	protected $multiTables = 'pol_parties t JOIN sys_regions r ON r.id = t.owner';
	protected $partyRegionsTableName = 'pol_party_regions';

	public function loadByRegion($region, $order = '') {
		$parties = $this->db->query('SELECT t.* FROM '.$this->tableName.' t JOIN '.$this->partyRegionsTableName.' r ON t.id = r.party WHERE r.region=%i '.$order, $region)->fetchAllRows();
		$partyList = new ObjectList(get_class());
		foreach ($parties as $party) {
			$obj = new Party();
			$obj->loadFromArray($party);
			$partyList->add($obj);
		}
		return $partyList;
	}

	//todo DELETE
	public static function getDropDownPartiesAll($where = '', $order = '') {
		$p = new Party();
		$ps = $p->getList($where = $where, $order = $order);

		$result = array();
		foreach($ps as $p) {
			$result[$p->id] = $p->name;
		}
		return $result;
	}

    public static function getDropDownAllParties($join = '',$where = '', $order = '') {
  		$p = new Party();
		$ps = $p->getList($join = $join, $where = $where, $order = $order);

		$result = array();
		foreach($ps as $p) {
			$result[$p->id] = $p->name;
		}
		return $result;
        }
	/**
	 * Returns Smarty ready list of parties selected by $region.
	 *
	 * @param Region|integer $region associated region, null to fetch all parties without restriction
	 * @param boolean $includeExpired include parties with expired functions in given region
	 * @return array (id => party name)
	 */
	public static function getDropDownParties($region = null, $includeExpired = false) {
		if($region !== null) {
			return DBs::inst(DBs::SYSTEM)->query('SELECT DISTINCT t.name, t.id FROM pol_parties t JOIN pol_party_regions r ON t.id = r.party WHERE r.region=%i'.(!$includeExpired ? ' AND r.time_end > now() ' : '').' ORDER BY t.name ASC', _id($region))->fetchAllCells('id');
		} else {
			return DBs::inst(DBs::SYSTEM)->query('SELECT DISTINCT t.name, t.id FROM pol_parties t '.(!$includeExpired? 'JOIN pol_party_regions r ON t.id = r.party ': '').' WHERE '.(!$includeExpired ? ' r.time_end > now() ' : ' TRUE').' ORDER BY t.name ASC')->fetchAllCells('id');
		}
	}

	/**
	 * List parties associated with given $region.
	 *
	 * The association follows the rules:
	 *   - if $region === null then fetch all parties
	 *   - if $region === false then fetch all partiesm that don't belong to any region
	 *   - otherwise take $region as region id, returns parties that belong to specified region
	 *
	 * Warning because of legacy or Record class there is no other way to specify ordering
	 * than by direct $order clause. Correct SQL syntax is expected!
	 *
	 * @param integer|boolean|null $region the region the party should belong to
	 * @param string $order the order clause, will be put as-is, so 'ORDER BY' is required
	 * @param integer $offset specify offset in the list if paged
	 * @param integer $limit limit result to first $limit occurences
	 * @return array of Party
	 */
	public static function listParties($region = null, $order = '', $offset = 0, $limit = 0) {
		$party = new Party();
		if($region === null) $where = '';
		elseif($region === false) $where = 'WHERE t.id NOT IN (SELECT party FROM pol_party_regions)';
		else $where = 'WHERE t.id IN (SELECT party FROM pol_party_regions WHERE region = '.(integer)$region.')';

		$limit = intval($limit) > 0 && intval($offset) >= 0? 'LIMIT '.intval($limit).' OFFSET '.intval($offset): '';
		return $party->getList('', $where, $order, $limit);
	}

	/**
	 * List active parties associated with given $region.
	 *
	 * @param integer|boolean|null $region the region the party should belong to
	 * @param string $order the order clause, will be put as-is, so 'ORDER BY' is required
	 * @param integer $offset specify offset in the list if paged
	 * @param integer $limit limit result to first $limit occurences
	 * @return array of Party
	 */
	public static function listActiveParties($region, $order = '', $offset = 0, $limit = 0) {
		$party = new Party();
		$where = 'WHERE t.id IN (SELECT party FROM pol_party_regions WHERE region = '.(integer)$region.' AND now() < time_end AND now() > time_start)';
		$limit = intval($limit) > 0 && intval($offset) >= 0? 'LIMIT '.intval($limit).' OFFSET '.intval($offset): '';
		return $party->getList('', $where, $order, $limit);
	}

	/**
	 * Returns valid party registration for given region.
	 *
	 * @param Region|integer $region region id
	 * @return LocalParty local party or null if not defined
	 */
	public function getRegionalParty($region) {
		$lp = new LocalParty();
		$list = $lp->getList('', 'WHERE now() < time_end AND party = '.$this->id.' AND region = '._id($region));
		if (count($list) == 0) return NULL;
		elseif(count($list) > 1) trigger_error("Database inconsistent, party has >1 valid registration in region: {$region} for party: {$this->id}!", E_USER_WARNING);
		else return array_pop($list);
	}

	/**
	 * Count all parties that belong to specified $region.
	 *
	 * The association follows the rules:
	 *   - if $region === null then fetch all parties
	 *   - if $region === false then fetch all partiesm that don't belong to any region
	 *   - otherwise take $region as region id, returns parties that belong to specified region
	 *
	 * @see listParties()
	 * @param integer|boolean|null $region the region the party should belong to
	 * @return integer total number of parties associated with given $region
	 */
	public static function countParties($region = null) {
		$party = new Party();
		if($region === null) $where = '';
		elseif($region === false) $where = 'WHERE t.id NOT IN (SELECT party FROM pol_party_regions)';
		else $where = 'WHERE t.id IN (SELECT party FROM pol_party_regions WHERE region = '.(integer)$region.')';

		return $party->getCount('', $where);
	}



	/**
	 * List voting parties for a raadsstuk.
	 * This method is used when you vote as a whole party and not per politician.
	 *
	 * @param string $vote_date the date as 'yyyy-mm-dd' string
	 */
	public function listPotentialVotingParties($vote_date) {
		//shitty hack indeed

		//return $p->getList('JOIN pol_party_regions pr ON pr.party = t.id JOIN pol_politicians po ON po.def_party', $p->db->formatQuery('WHERE %s BETWEEN pr.time_start AND pr.time_end', $vote_date));
		$parties = $this->db->query('SELECT t.id, t.name FROM pol_parties t JOIN pol_party_regions pr ON pr.party = t.id JOIN pol_politicians po ON po.def_party = t.id')->fetchAllRows();

		$vote_parties = array();
		foreach ($parties as $vtpt) {
			$vote_parties[$vtpt['name']] = array('id' => $vtpt['id'], 'vote');
		}
	}

	/**
	 * Get all raadsstukken this party has voted for. Individual as well as whole party votes
	 *
	 * @return array of Raadsstuks
	 */
	public function getRaadsstukken($region = false, $limit = false) {
		$rsObjects = array();

		if($region) {
			$where = ' WHERE v.party = %i AND rs.region = '.$region->id;
		} else {
			$where = ' WHERE v.party = %i ';
		}

		$query = 'SELECT DISTINCT rs.id, rs.vote_date
					FROM rs_votes v
					JOIN rs_raadsstukken rs
					ON v.raadsstuk = rs.id
					'.$where.'
					ORDER BY rs.vote_date DESC, rs.id DESC';
		if($limit) {
			$query .= ' LIMIT '.$limit;
		}

		$raadsstukken = $this->db->query($query, $this->id)->fetchAllRows();

		foreach($raadsstukken as $rs) {
			$rsObjects[] = new Raadsstuk($rs['id']);
		}

		return $rsObjects;
	}

	public function getCommonRaadsstukCount($party_id, $raadsstukken){
		$rsString = implode(', ', $raadsstukken);
		$query = 'SELECT COUNT(DISTINCT raadsstuk) FROM rs_votes t WHERE t.party = %i AND t.raadsstuk IN ('.$rsString.')';

		$result =  $this->db->query($query, $party_id)->fetchCell();
		return $result;
	}
}

?>
