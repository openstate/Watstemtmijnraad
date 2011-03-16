<?php

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

    public function getByUserIds($user_ids) {
        if(!is_array($user_ids))
            return;
        $db = DBs::inst(DBs::SYSTEM);
        $roles = array();
        $results = $db->query('SELECT ur.id, t.title, ur.userid AS userid, s.title AS site_name
                    FROM usr_bo_roles t 
                    JOIN sys_site s ON s.id = t.site_id 
                    LEFT JOIN usr_bo_users_roles ur ON ur.roleid = t.id  
                    WHERE userid IN ('. implode(', ', $user_ids) . ')')->fetchAllRows();
        foreach($results as $result) {
            $roles[$result['userid']][] = $result['title'];
        }
        
        return $roles;
    }

	public function getAllRoles() {
		return $this->getList();
	}
}


?>