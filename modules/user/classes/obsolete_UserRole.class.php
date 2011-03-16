<?php

//[FIXME: dublicate class (classes/user)! check if it is still used ]
class UserRole extends Record {
	protected $tableName = 'usr_bo_roles';
	protected $multiTables = 'usr_bo_roles t JOIN sys_site s ON s.id = t.site_id LEFT JOIN usr_bo_users_roles ur ON ur.roleid = t.id';
	protected $data = array(
					'userid' => null,
					'title' => null,
					'site_name' =>null,
				);
	protected $extraCols = array(
					'userid' => 'ur.userid',
					'site_name' => 's.title'
				);


	public function getAllRoles() {
		return $this->getList();
	}
}


?>