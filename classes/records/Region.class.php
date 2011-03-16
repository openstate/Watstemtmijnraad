<?php
require_once('Style.class.php');
require_once 'HNSSyncedRecord.class.php';

class Region extends HNSSyncedRecord {
	const RETURN_OBJECT = 'object';

	public static $LEVELS = array(
		'Internationaal'	=> 1,
		'Landelijk'			=> 2,
		'Provincie'			=> 3,
		'Gemeente'			=> 4,
		'Stadsdeel'			=> 5,
	);

	protected $data = array(
		'name'        => null,
		'abbreviation' => null,
		'subdomain'   => null,
		'level'       => null,
		'parent'      => null,
		'level_name'  => null,
		'parent_name' => null,
		'used_wizard' => 0,
        'hidden'      => false,
	);
	protected $extraCols = array(
		'level_name'  => 'l.name',
		'parent_name' => 'p.name');

	protected $multiTables = '
		sys_regions t
		JOIN sys_levels l ON t.level = l.id
		LEFT JOIN sys_regions p ON t.parent = p.id';

	protected $tableName = 'sys_regions';

    public static function loadById($id, $returnObject = false) {
        $db = DBs::inst(DBs::SYSTEM);
        $result = $db->query('SELECT t.* FROM sys_regions t WHERE t.id = %i', $id)->fetchRow();
        if (!$returnObject) return $result;

		$region = new Region();

		$region->loadFromArray($result);

		return $region;
    }

	/**
	 * Find region for a given $subdomain.
	 *
	 * This method is used by <tt>Dispatcher</tt> to change current region
	 * according to subdomain being requested.
	 *
	 * Note: for no subdomain request this method returns null, so the region
	 * restrictions will be disabled for the whole site.
	 *
	 * @param string $subdomain subdoman
	 * @return Region fetched region or null if not found
	 */
	public function loadBySubdomain($subdomain) {
		$result = $this->getList('', 'WHERE '.$this->db->formatQuery('t.subdomain = %s', strtolower($subdomain)));
		if (count($result)) return reset($result);
		return null;
	}

	/**
	 * Returns Smarty ready list of regions having at least one raadsstuk associated.
	 * @return array (id => region name)
	 */
	public static function getDropDownRegions() {
		$ids = array_keys(Dispatcher::sessionUser() ? Dispatcher::sessionUser()->listSiteIds(): User::listDefaultSiteIds());
		return DBs::inst(DBs::SYSTEM)->query('SELECT DISTINCT t.name, t.id, l.name||\' \'||p.name AS parent_name FROM sys_regions t JOIN sys_regions p JOIN sys_levels l ON l.id = p.level ON t.parent = p.id JOIN rs_raadsstukken r ON t.id = r.region WHERE t.level >= 4 AND r.site_id IN ('.implode(', ', $ids).') AND t.hidden = 0 GROUP BY t.name, t.id, parent_name ORDER BY t.name ASC')->fetchAllCells('id', 'parent_name');
	}

	/**
	 * Returns Smarty ready list of regions having at least one raadsstuk associated.
	 * @param $level String name for selecting one level
	 * @return array (id => region name)
	 */
	public static function getDropDownRegionsByLevel($level) {
		$levelId = Region::getLevel($level);
		$ids = array_keys(Dispatcher::sessionUser() ? Dispatcher::sessionUser()->listSiteIds(): User::listDefaultSiteIds());
		return DBs::inst(DBs::SYSTEM)->query('SELECT DISTINCT t.name, t.id, l.name||\' \'||p.name AS parent_name FROM sys_regions t JOIN sys_regions p JOIN sys_levels l ON l.id = p.level ON t.parent = p.id JOIN rs_raadsstukken r ON t.id = r.region WHERE t.level = '.$levelId.' AND r.site_id IN ('.implode(', ', $ids).') GROUP BY t.name, t.id, parent_name ORDER BY t.name ASC')->fetchAllCells('id', 'parent_name');
	}

	/**
	 * List regions of level > 2.
	 * @return array of Region
	 */
	public static function listProvincialRegions() {
		$region = new Region();
		return $region->getList(
			'JOIN sys_regions reg ON reg.parent = t.id',
			'WHERE t.level > 2',
			'ORDER BY t.level ASC, p.name ASC, t.name ASC');
	}

	/**
	 * List the whole region tree using in depth search.
	 * This list is used to build the HTML list.
	 *
	 * @return array list of (id, name, level, parent) tuples, ordered by in-depth search
	 */
	public static function listInDepthRegions() {
		//[FIXME: nested-set will be better here... =/ ]
		//all regions grouped by parent_id
		$r = new Region();
		$allregs = $r->db->query('SELECT id, name, level, (CASE WHEN parent IS NULL THEN \'0\' ELSE parent END) AS parent_id FROM sys_regions ORDER BY level')->fetchAllRows(false, 'parent_id');

		$list = array();
		self::collectSubRegions($allregs, 0, $list);
		return $list;
	}

	private static function collectSubRegions($allregs, $parentId, &$list) {
		if(isset($allregs[$parentId])) { //if this is not a leaf
			foreach($allregs[$parentId] as $region) {
				$list[] = array(
					'id' => $region['id'],
					'name' => $region['name'],
					'level' => $region['level'],
					'parent' => $region['parent_id']
				);
				self::collectSubRegions($allregs, $region['id'], $list);
			}
		}
		return $list;
	}


	/**
	 * Returns Smarty ready list of all regions registered in the system.
	 * @return array (id => region name)
	 */
	public static function getDropDownRegionsAll() {
		$region = new Region();
		$regions = $region->getList('', '', 'ORDER BY t.level ASC, p.name ASC, t.name ASC');

		$result = array();
		foreach($regions as $key => $region) {
			if ($region->level < 3)
				$result[$key] = $region->name;
			else
				$result[$regions[$region->parent]->formatName()][$key] = $region->name;
		}
		return $result;
	}

	/**
	 * Returns Smarty ready list of all regions registered in the system.
	 * @param $level String name for selecting one level
	 * @return array (id => region name)
	 */
	public static function getDropDownRegionsAllByLevel($level, $order = 'ORDER BY t.level ASC, p.name ASC, t.name ASC') {
		//leven names are never nummeric, id's are
		$levelId = is_numeric($level)? intval($level): Region::getLevel($level);

		$region = new Region();
		$regions = $region->getList('JOIN rs_raadsstukken rsr ON t.id = rsr.region', 'WHERE t.level = '.$levelId.' AND t.hidden = 0', $order);

		$result = array();
		foreach($regions as $key => $region) {
			$result[$key] = $region->name;
		}

		return $result;
	}


	/**
	 * Select descendants of this region with at least one raadsstuk.
	 * @return list of Region objects
	 */
	public function getActiveChildrenUndeepWithoutRaadsstukCount() {
		if(!$this->id) return array();
		return $this->getList('', $this->db->formatQuery('WHERE t.parent = %i AND t.id IN (SELECT r.region FROM rs_raadsstukken r) AND t.hidden = 0', $this->id));
	}

	/**
	 * Select descendants of this region.
	 *
	 * If $deep is set to false, then faster query is used to select direct
	 * children only. If $raadsstuk_count is set to false, then raadsstuks will not
	 * be counted, this greatly increases the performance.
	 *
	 * @param boolean $deep true - select all descendants, false -- select children only
	 * @param boolean $raadsstuk_count true - select number of raadsstuks for each region, false - don't select
	 * @param boolean $active true - only select regions with raadsstuks, false - select all childrens
	 * @return array (id => region) or (id => ['region' => region, 'count' => count])
	 */
	public function selectChildren($deep = false, $raadsstuk_count = false, $no_hidden = false) {
		$ids = implode(', ', array_keys(Dispatcher::sessionUser() ? Dispatcher::sessionUser()->listSiteIds(): User::listDefaultSiteIds()));

		if($raadsstuk_count) {
			$fuck = array_merge(array_values($this->extraCols), array_map(create_function('$val', 'return "t.{$val}";'), array_diff(array_merge(array('id'), array_keys($this->data)), array_keys($this->extraCols))));
			$fuck[] = 't.hidden';
			$group = implode(', ', $fuck);
		}

		if(!$deep) {
			if($raadsstuk_count) {
				//this is most ridiculous hack I've made so far)))
				$parts = $this->getParts();
				$rows = $this->db->query('SELECT t.*'.$parts['extra'].', COUNT(r.id) as raadsstuk_count FROM '.$parts['tables']." LEFT JOIN rs_raadsstukken r ON t.id = r.region AND r.site_id IN({$ids}) WHERE t.parent = {$this->id} GROUP BY {$group}")->fetchAllRows();

				$result = array();
				foreach ($rows as $row) {
					$obj = new self();
					$obj->loadFromArray($row);
					$result[$obj->id]= array('region' => $obj, 'count' => $row['raadsstuk_count']);
				}

				return $result;
			} else return $this->getList("WHERE t.parent = {$this->id} ".($no_hidden? ' AND t.hidden = 0': '')." ORDER BY t.name");
		} else {
			if($raadsstuk_count) {
				$parts = $this->getParts();
				$query = array(
					'SELECT t.*'.$parts['extra'].', COUNT(r.id) as raadsstuk_count FROM '.$parts['tables']." LEFT JOIN rs_raadsstukken r ON t.id = r.region AND r.site_id IN({$ids}) WHERE t.parent IN (",
					") GROUP BY {$group}"
				);
			} else $query = null;

			$result = array();
			$this->selectChildrenSubQuery((string)$this->id, $query, $result);
			return $result;
		}
	}

	/**
	 * Count number of direct children
	 * @return integer
	 */
	public function countChildren() {
		if($this->id == null) return null;
		return $this->db->query('SELECT COUNT(*) FROM sys_regions WHERE parent = %i', $this->id)->fetchCell();
	}

	/** Inefficient recursive tree traversal. */
	private function selectChildrenSubQuery($parents, $query, &$result) {
		//[FIXME: nested set will be better here]
		if($query == null) {
			$chlds = $this->getList('', "WHERE t.parent IN ($parents)");
			$lev = array();
			foreach ($chlds as $chld) {
				$result[$chld->id] = $chld;
				$lev[] = $chld->id;
			}
			if($lev) $this->selectChildrenSubQuery(implode(', ', $lev), $query, $result);
		} else {
			$q = implode($parents, $query);
			$rows = $this->db->query($q)->fetchAllRows();
			$lev = array();
			foreach ($rows as $row) {
				$obj = new self();
				$obj->loadFromArray($row);
				$result[$obj->id]= array('region' => $obj, 'count' => $row['raadsstuk_count']);
				$lev[] = $obj->id;
			}
			if($lev) $this->selectChildrenSubQuery(implode(', ', $lev), $query, $result);
		}
	}

	/**
	 * Returns list of id's containing $parent and ids of all descendant regions.
	 *
	 * @param integer $parent parent node, null to fetch entire tree
	 * @return array list of region id's in unspecified order
	 */
	public function selectSubtreeIDs($parent = null) {
		if($parent === null) return $this->db->query('SELECT id FROM sys_regions')->fetchAllCells();

		$ret = array($parent);
		$this->selectChildIDs(array(intval($parent)), $ret);
		return $ret;
	}

	public function selectChildIDs($parents, &$ret) {
		$ids = $this->db->query('SELECT id FROM sys_regions WHERE parent IN ('.implode(', ', $parents).')')->fetchAllCells();
		if($ids) {
			$ret += $ids;
			$this->selectChildIDs($ids, $ret);
		}
	}


	/**
	 * Format name of this region.
	 * @return string region name and optionally level name
	 */
	public function formatName() {
		return Region::formatRegionName($this->name, $this->level, $this->level_name);
	}

	/** Appends level name to provincial and municipal regions */
	public static function formatRegionName($regionName, $level, $levelName) {
		//[FIXME: unusefull method]
		return ($level > 2 ? $levelName.' ' : '').$regionName;
	}

	/**
	 * Gets the level ID of the level you want the ID to know of
	 * @param $levelName Name of the region level
	 * @return id of the Level
	 */
	public static function getLevel($levelName) {
		return DBs::inst(DBs::SYSTEM)->query('SELECT id FROM sys_levels WHERE name=\''.$levelName.'\'')->fetchCell();
	}

	public function toXml($xml) {
		return $xml->getTag('region').
			$xml->fieldToXml('id', $this->id, false).
			$xml->fieldToXml('name', $this->name, true).
			((null !== $this->parent) ? $xml->fieldToXml('parent', $this->parent, false) : '').
			$xml->getTag('region', true);
	}

	/**
	 * Gets the logo of this region, that is stored in a style.
	 * @return String with the filename of the logo or False
	 */
	public function getLogo() {
		try {
			$style = new Style($this->id);
		} catch (NoSuchRecordException $e) {
			return false;
		}

		if($style->logo && $style->logo != '') {
			return $style->logo;
		} else {
			return false;
		}
	}


	/**
	 * List regions for givne party.
	 *
	 * @param integer $party id
	 * @return list of Region objects
	 */
	public static function listForParty($party) {
		$r = new Region();
		return $r->getList('', $r->db->formatQuery('WHERE t.id IN (SELECT region FROM pol_party_regions WHERE party = %i AND now() < time_end AND now() > time_start)', _id($party)), 'ORDER BY t.name, t.level');
	}

	///////////////////////////////////////////////////////
	//                                                   //
	//     HNS.dev SYNCHRONISATION RELATED FUNCTIONS     //
	//                                                   //
	///////////////////////////////////////////////////////

	public function __get($key) {
		switch ($key) {
			case 'hns_parent_id':
				return $this->getParentHnsId();
			default:
				return parent::__get($key);
		}
	}

	public function verifyCanSaveInHns() {
		throw new HnsCannotSaveError('Regions cannot be saved/updated to HNS');
	}

	protected function getParentHnsId() {
		$parentId = $this->parent;

		if (empty($parentId)) {
			throw new Exception('Cannot fetch parent for toplevel region');
		}

		$parent = $this->loadById($parentId, self::RETURN_OBJECT);

		if (!$parent->hasHnsId()) $parent->save();

		return $parent->hnsId();
	}

	protected function getHnsMapping() {
		$mapping = array(
			'hnsTable' => 'region',
			'fields' => array(
				'name'   => 'name',
				'level'  => 'level',
				'hns_parent_id' => 'parent',
				'hidden' => 'hidden'
			)
		);

		return $mapping;
	}

	protected $uniques = array(
		'name'   => 'name',
		'level'  => 'level',
		'hns_parent_id' => 'parent'
	);

	protected function getHnsUniqueCheck() {
		if ($this->parent == false) unset($this->uniques['hns_parent_id']);

		return $this->uniques;
	}
}

?>
