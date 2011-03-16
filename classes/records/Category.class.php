<?php
require_once('ObjectList.class.php');

/**
* Single category.
* Describes function (appointment) of a politician (1:*) and raadsstukken (*:*).
*/
class Category extends Record {

	protected $data = array(
		'name'        => null,
		'description' => null,
	);

	protected $ghostCols = array(
		'level_description' => null //available if listed by Region
	);

	protected $tableName = 'sys_categories';
	protected $categoryRegionsTableName = 'sys_category_regions';

	/**
	 * Returns list of categories of the level specified by given region.
	 *
	 * @see listByRegionLevel(int)
	 * @param Region|integer $region the region to fetch the level
	 * @return array of Category categories at specified levels
	 */
	public static function listByRegion($region, $limit = NULL) {
		if(is_object($region)) return Category::listByRegionLevel($region->level);

		//OK, we need to join region table as well
		$cat = new Category();
		$cat->extraCols['level_description'] = 'cr.description';

		return $cat->getList(
			'JOIN '.$cat->categoryRegionsTableName.' cr ON t.id = cr.category',
			$cat->db->formatQuery('WHERE cr.level = (SELECT level FROM sys_regions r WHERE id = %i)', $region),
			'ORDER BY t.name',
			($limit? $cat->db->formatQuery('LIMIT %i', $limit): ''),
			''
		);
	}

	/**
	 * Returns plain list of id => [id, name, count], where count is the number of
	 * raadsstukken having that category.
	 *
	 * @param Region|integer $region the region
	 * @return array of [id, name, count] 
	 */
	public static function listPlainByRegion($region, $party = null, $limit = NULL) {
		//zaebalo
		$db = DBs::inst(DBs::SYSTEM);
		$pt = $party? $db->formatQuery('AND r.id IN (SELECT raadsstuk FROM rs_votes WHERE party = %i)', _id($party)): '';
		
		$query = "SELECT stt.* 
				  FROM (SELECT st.* FROM (
							SELECT ct.id, ct.name, COUNT(*) as count
							FROM sys_categories ct
							JOIN rs_raadsstukken_categories rc ON rc.category = ct.id
							JOIN rs_raadsstukken r ON rc.raadsstuk = r.id
							WHERE r.region = %i %l
							GROUP BY ct.id, ct.name
							HAVING COUNT(*) > 0
						) st ORDER BY st.count DESC %l
				  ) stt
				  ORDER BY stt.name";
		
		return $db->query($query, _id($region), $pt, ($limit? $db->formatQuery('LIMIT %i', $limit): ''))->fetchAllRows();
	}

	/**
	 * Returns list of categories of the speciied region level.
	 *
	 * @param Level|integer $region the region to fetch the level
	 * @return array of Category categories at specified levels
	 */
	public static function listByRegionLevel($level) {
		$level = is_object($level)? $level->level: intval($level);

		$cat = new Category();
		$cat->extraCols['level_description'] = 'cr.description';

		return $cat->getList(
			'JOIN '.$cat->categoryRegionsTableName.' cr ON t.id = cr.category',
			$cat->db->formatQuery('WHERE cr.level = %i', $level),
			'ORDER BY t.name'
		);
	}


	/**
	 * List all level bound descriptions.
	 *
	 * Method lists all levels. If there is no description for that level, then
	 * 'description' will be NULL. Having description at specific level (non NULL) means
	 * the category will be accessible at this level.
	 *
	 * @return array (int level => ('description', 'level_name', 'level'))
	 */
	public function listLevelDescriptions() {
		$c = new Category($this->id);
		//[FIXME: direct link to sys_levels!]
		$catLevels = $this->db->query('SELECT l.*, cr.* FROM sys_levels l LEFT JOIN '.$this->categoryRegionsTableName.' cr ON l.id = cr.level AND cr.category = %i ORDER BY l.id', $this->id)->fetchAllRows();

		$ret = array();
		foreach ($catLevels as $lev) {
			$ret[$lev['id']] = array('description' => $lev['description'], 'level_name' => $lev['name'], 'level' => $lev['id']);
		}

		return $ret;
	}

	/**
	 * Set level descriptions of this catetegory (register category at given levels).
	 *
	 * The $descriptions is expected to be (level_id => description). If description is null,
	 * or level is not set, then category will be unregistered from that level.
	 *
	 * @throws Exception on database commit error
	 * @param array $descriptions level descriptions as (level => description)
	 */
	public function setLevelDescriptions($descriptions) {
		$this->db->query('BEGIN');
		try {
			$this->db->query('DELETE FROM '.$this->categoryRegionsTableName.' WHERE category = %i', $this->id);
			foreach ($descriptions as $lev_id => $descr) {
				$this->db->query('INSERT INTO '.$this->categoryRegionsTableName.' (category, level, description) VALUES (%i,%i,%s)', $this->id, $lev_id, $descr);
			}
			$this->db->query('COMMIT');
		} catch (Exception $e) {
			$this->db->query('ROLLBACK');
			throw $e;
		}
	}

	/**
	 * List categories in array.
	 * @return array (id => name) list of categories, direct usable by smarty
	 */
	public static function getDropDownCategoriesAll() {
		$c = new Category();
		$cs = $c->getList('', '', 'ORDER BY t.name');

		$result = array();
		foreach($cs as $c) {
			$result[$c->id] = $c->name;
		}
		return $result;
	}


	/** Serialize to XML */
	public function toXml($xml) {
		return $xml->getTag('category').
			$xml->fieldToXml('id', $this->id, false).
			$xml->fieldToXml('name', $this->name, true).
			$xml->getTag('category', true);
	}
}

?>