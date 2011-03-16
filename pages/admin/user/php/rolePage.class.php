<?php

require_once('BackofficeUser.class.php');
require_once('UserRole.class.php');
require_once('Role.class.php');
require_once('Level.class.php');
require_once('Politician.class.php');
require_once('LocalParty.class.php');
require_once('Region.class.php');

class rolePage {
	private $id = null;
	private $regions = null;

 	protected $pager;
	protected $politicians;
	protected $localparties;
    protected $contain_errors = false;
  
	public function processGet($get) {
		try {
			if(!isset($get['id']) || !ctype_digit($get['id'])) Dispatcher::badRequest();
			$this->user = new BackofficeUser();
			$this->user->load(intval($get['id']));
		} catch (Exception $e) {
			Dispatcher::notFound();
		}

		if(!isset($get['id']) || !ctype_digit($get['id']))
			Dispatcher::header('../');
        $user = Dispatcher::inst()->user;
		$p = new Politician();

		$this->politicians = $p->loadByBoUser($user->id, "ORDER BY id");
		$this->localparties = $user->listAllowedParties();
		$allowed_regions = $user->listAllowedRegions();

		$dataset = array();
		$region = new Region();
		$all_regions = $region->getList('','','ORDER BY level ASC, name');

		foreach($all_regions as $region) {
			$dataset[$region->id] = array(
				'id'		=> $region->id,
				'level'		=> $region->level,
				'parent'	=> $region->parent,
				'name'		=> $region->name,
				'show'		=> true,
				'show_link'	=> true,
				'children'	=> array(),
			);
		}

		/**
		 *	This piece is from http://www.tommylacroix.com/2008/09/10/php-design-pattern-building-a-tree/
		 */
		$tree = array();
		foreach ($dataset as $id=>$node) {
			//[FIXME: id == 0 is not used in postgres]
			if ($node['parent'] == null) { // root node
				$tree[$id] = &$dataset[$id];
			} else { // sub node
				$dataset[$node['parent']]['children'][$id] = &$dataset[$id];
			}
		}

		$this->regions = $tree;
		$this->id = $get['id'];
	}
	
	public function processPost($post) {

        try {
			$this->user->setAllowedRegions(@$post['regions']);
		} catch (Exception $e) {
			$this->contain_errors = true;
		}
		
		if(!isset($post['userid']) || !ctype_digit($post['userid']) || !is_array($post['roles']))
			Dispatcher::header();
		
		$user = new BackofficeUser();
		$user->load($post['userid']);
							
		$user->rights->clearRoles(); //delete
		foreach($post['roles'] as $roleID) {
			$user->rights->addRole($roleID);
		}
		$user->rights->saveRoles();
	}

	public function show($smarty) {
		
		$user = new BackofficeUser();
		$user->load($this->id);
		
		$smarty->assign('user', $user);
		
		$userRole = new UserRole();
		$allRoles = $userRole->getAllRoles();
		
		$smarty->assign('userid', $this->id);
		
		$roles = array();
		foreach($allRoles as $key => $role) {
			if($role->id != 1)
				$roles[$key] = "{$role->title} ({$role->site_name})";
		}		
		$smarty->assign('roles', $roles);
		
		$userRoles = $userRole->getList($where = 'WHERE userid = ' . $this->id);

		$smarty->assign('politicians', $this->politicians);
		$smarty->assign('localparties', $this->localparties);
		$smarty->assign('count', count($this->politicians) + count($this->localparties) + count($this->regions));
		$smarty->assign('selectedRoles', array_keys($userRoles));
        $smarty->assign('template_path', $smarty->getCurrDir().'../content/');
		$smarty->assign('levels', Level::listListOrdered());
		$smarty->assign('regions', $this->regions);
		$smarty->assign('selectedRegions', $this->user->listAllowedRegions());
		$smarty->assign('user', $user);
		$smarty->display('rolePage.html');
	}
}

?>