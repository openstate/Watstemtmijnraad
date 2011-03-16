<?php

require_once('Pager.class.php');
require_once('Politician.class.php');
require_once('LocalParty.class.php');
require_once('Region.class.php');

class indexPage {
	protected $pager;
	protected $politicians;
	protected $localparties;
	protected $regions;

	public function processGet($get) {
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
				'show'		=> false,
				'show_link'	=> false,
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
				if ($user->isSuperAdmin() || array_key_exists($node['id'], $allowed_regions)) {
					$dataset[$id]['show_link'] = true;

					$pt = $id;
					while($pt) {
						$dataset[$pt]['show'] = true;
						$pt = $dataset[$pt]['parent'];
					}
				}
			}
		}

		$this->regions = $tree;
	}

	public function show($smarty) {
		$smarty->assign('politicians', $this->politicians);
		$smarty->assign('localparties', $this->localparties);
		$smarty->assign('regions', $this->regions);
		$smarty->assign('count', count($this->politicians) + count($this->localparties) + count($this->regions));
		$smarty->assign('template_path', $smarty->getCurrDir().'../content/');
		$smarty->display('indexPage.html');
	}
}

?>
