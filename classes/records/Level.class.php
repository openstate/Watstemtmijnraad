<?php

/** Region level names. */
class Level extends Record {

	protected $data = array('name' => null);
	protected $tableName = 'sys_levels';

	//ordered set of levels
	//  1 - International
	//  2 - Landelijk
	//  3 - etc
	//
	// it is stupid that primary key is used for level number, we may not make any mistakes now...


	/**
	 * Returns list of level objects ordered by level (id).
	 * @return array of Level
	 */
	public static function listListOrdered() {
		$lst = new Level();
		return $lst->getList('', '', 'ORDER BY id');
	}

	/** Returns name for given level */
	public static function getName($level) {
		//[FIXME: hardcode instead of DB query!]
		$db = DBs::inst(DBs::SYSTEM);
		return $db->query('SELECT name FROM sys_levels WHERE id = %i', intval($level))->fetchCell();
	}

	/**
	 * Read only access to 'level' field.
	 * The level is currently jsut an alias for 'id'.
	 *
	 * @param string $name variable name
	 * @return mixed
	 */
	public function __get($name) {
		if($name == 'level') return $this->id;
		return parent::__get($name);
	}
}

?>