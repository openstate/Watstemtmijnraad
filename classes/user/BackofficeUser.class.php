<?php

require_once('User.class.php');

class BackofficeUser extends User {
	protected $tableName = 'usr_bo_users';
	protected $cookieName = 'bo_user';

	protected static $gettext = null;
	protected $superAdmin = null;
	protected $sites = null;
	protected $sites_obj = null;

	public $defaultRoles = array('1');
	protected $data = array(
		'firstname' => '',
		'lastname'  => '',
		'gender_is_male' => ''
	);

	public function formatName() {
		return FrontofficeUser::formatUserName($this->firstname, $this->lastname, $this->gender_is_male);
	}

	public static function formatUserName($firstName, $lastName, $gender) {
		if (!BackofficeUser::$gettext) {
			BackofficeUser::$gettext = new GettextPO($_SERVER['DOCUMENT_ROOT'].'/../locale/'.
			(Dispatcher::inst()->locale ? Dispatcher::inst()->locale : 'nl').'/title.po');
		}
		if ($firstName != null)
			return $firstName.' '.$lastName;
		else
			return BackofficeUser::$gettext->getMsgstr('title.'.($gender ? 'male' : 'female')).' '.ucfirst($lastName);
	}

	public function formatUserSortName() {
		$namePrefix = '';
		if (preg_match('/^([^A-Z]+)([A-Z].*)$/', $this->lastname, $matches)) {
			$namePrefix = trim($matches[1]);
			$lastName = trim($matches[2]);
		} else {
			$lastName = $this->lastname;
		}

		$genderTitle = ''; //Politician::gettext()->getMsgstr('title.'.($gender ? 'male' : 'female'));
		if ($this->firstname != null)
			return $lastName.', '.$this->firstname. ($namePrefix ? ' '.$namePrefix : '');
		else
			return $lastName.' '. ($namePrefix ? ' '.ucfirst($namePrefix) : '');
	}

	public function isSuperAdmin() {
		if (null === $this->superAdmin) {
			$roles = $this->db->query('SELECT r.* FROM '.$this->tableName.'_roles ur JOIN usr_bo_roles r ON ur.roleid = r.id WHERE ur.userid='.(int) $this->id)->fetchAllRows();
			foreach ($roles as $role) {
				if ($role['title'] == 'superadmin') {
					$this->superAdmin = true;
					return true;
				}
			}
			$this->superAdmin = false;
		}
		return $this->superAdmin;
	}

	public function canEditParty(Party $p) {
		if ($this->isSuperAdmin()) return true;
		if(isset($_SESSION['role'])) {
			$region = $_SESSION['role']->getRecord();
			return $region->id == $p->owner;
		}
		return false;
	}


	/**
	 * List all sites where this user has role in.
	 * @return array list of site id's
	 */
	public function listSiteIds() {
		if($this->sites == null) {
			$roles = $this->db->query('SELECT r.* FROM '.$this->tableName.'_roles ur JOIN usr_bo_roles r ON ur.roleid = r.id WHERE ur.userid='.(int) $this->id)->fetchAllRows();

			$ret = array();
			foreach ($roles as $role) $ret[$role['site_id']] = true;
			$this->sites = $ret;
		}
		return $this->sites;
	}

	/**
	 * Returns list of Site's associated to this user by the roles.
	 * @return array list of Site objects
	 */
	public function listSites() {
		if(!class_exists('Site')) require_once('Site.class.php');

		if($this->sites_obj == null) {
			$ids = $this->listSiteIds();

			if(!empty($ids)) {
				$ret = new Site();
				$this->sites_obj = $ret->getList(null, 'WHERE id IN ('.implode(', ', $ids).')');
			} else return array();
		}
		return $this->sites_obj;
	}


	/**
	 * List regions allowed to edit by this backoffice user.
	 * @return array of Region
	 */
	public function listAllowedRegions() {
		$r = new Region();
        if($this->isSuperAdmin())
            return $r->getList('', '', 'ORDER BY t.name');
        else
            return $r->getList('JOIN sys_region_users sru ON sru.region = t.id', 'WHERE sru.bo_user = '.$this->id, 'ORDER BY t.name');
	}


	/**
	 * Reset allowed regions.
	 * Method clears previous and sets new associations.
	 * @throws Exception on any error
	 * @param array $regions list of region id's to associate
	 */
	public function setAllowedRegions($regions) {
		$this->db->query('BEGIN');

		try {
			$this->db->query('DELETE FROM sys_region_users WHERE bo_user = ' . $this->id);
			if(is_array($regions)) {
				foreach($regions as $id) {
					$this->db->query('INSERT INTO sys_region_users (%l) VALUES (%l)', '"bo_user","region"', $this->id . ',' . intval($id));
				}
			}
		} catch(Exception $e) {
			$db->query('ROLLBACK');
			throw($e);
		}

		$this->db->query('COMMIT');
	}


	/**
	 * Returns list of all parties that may be edited by this user.
	 * @return array of LocalParty
	 */
	public function listAllowedParties() {
		$lp = new LocalParty();
		return $lp->getList('', $this->isSuperAdmin()? '': 'WHERE bo_user = '.$this->id, 'ORDER BY id');
	}
}

?>