<?php

class Tag extends Record {

	protected $data = array(
		'name' => null,
	);

	protected $tableName = 'sys_tags';

	public static function getAssociativeOnId() {
		$r = new self();
		
		$result = array();		
		foreach($r->getList() as $t) {
			$result[$t->id] = $t->name;
		}
		return $result;
	}

	public static function getAssociativeOnName() {
		$r = new self();
		
		$result = array();		
		foreach($r->getList() as $t) {
			$result[$t->name] = $t->id;
		}
		return $result;
	}

	public static function getNames() {
		$names = self::getAssociativeOnId();
		asort($names);
		return array_values($names);
	}
	
	/**
	 * Select tags by popularity, order alphabetically.
	 * @param integer $limit not more than $limit
	 */
	public static function listPopular($limit = NULL) {
		$db = DBs::inst(DBs::SYSTEM);
		return $db->query('SELECT st.* FROM (SELECT * FROM sys_tags WHERE count > 0 ORDER BY count DESC %l) st ORDER BY st.name', ($limit? $db->formatQuery('LIMIT %i', $limit): ''))->fetchAllRows();
	}
	
	/**
	 * Select tags by popularity, order alphabetically.
	 * @param integer $region of specific region
	 * @param integer $limit not more than $limit
	 */
	public static function listPopularByRegion($region, $party = null, $limit = NULL) {
		$db = DBs::inst(DBs::SYSTEM);
		$limit = ($limit? $db->formatQuery('LIMIT %i', $limit): '');
		
		$pt = $party? $db->formatQuery('AND r.id IN (SELECT raadsstuk FROM rs_votes WHERE party = %i)', _id($party)): '';
		$query = "	SELECT stt.* 
				  	FROM (SELECT st.* FROM (
						SELECT st.id, st.name, COUNT(*) as count
						FROM sys_tags st	
						JOIN rs_raadsstukken_tags rt ON rt.tag = st.id
						JOIN rs_raadsstukken r ON rt.raadsstuk = r.id
						WHERE r.region = %i %l
						GROUP BY st.id, st.name
						HAVING COUNT(*) > 0
					) st ORDER BY st.count DESC %l) stt
					ORDER BY stt.name;";
		
		return $db->query($query, _id($region), $pt, $limit)->fetchAllRows();
	}
	
	/**
	 * Select tags by popularity, order alphabetically.
	 * @param integer $party of specific party
	 * @param integer $region in specific region
	 * @param integer $limit not more than $limit
	 */
	public static function listPopularByParty($party, $region, $limit = NULL) {
		$db = DBs::inst(DBs::SYSTEM);
		
		return $db->query('SELECT st.* FROM (SELECT * FROM sys_tags ORDER BY count DESC %l) st ORDER BY st.name', ($limit? $db->formatQuery('LIMIT %i', $limit): ''))->fetchAllRows();
	}
}

?>