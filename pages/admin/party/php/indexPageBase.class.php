<?php

require_once('PartyVoteCache.class.php');

abstract class indexPageBase {
	protected $data;
	protected $votes;
	
	protected $dataLoaded = false;
	
	protected $sortDefault = 'id';
	protected $sortDirDefault = 'asc';
	protected $sorting = array('col' => '', 'dir' => 'asc');
	protected $sortKeys;
	

	public function __construct() {
		
		
		$this->sortKeys = array(
			'id' => false,
			'id' => null,
			'party_name' => null,

		);
		
		
	}


	protected function getOrder($order = '') {
		if ($order == '') { // Use ordering based on sort columns
			if (isset($_GET['sort']) && in_array($_GET['sort'], array('asc','desc')))
				$dir = $_GET['sort'];
			else
				$dir = $this->sortDirDefault;
			if (isset($_GET['sortcol']) && array_key_exists($_GET['sortcol'], $this->sortKeys))
				$sortCol = $_GET['sortcol'];
			else
				$sortCol = $this->sortDefault;

			$order = 'ORDER BY "'.$sortCol.'" '.$dir;
			$this->sorting['col'] = $sortCol;
			$this->sorting['dir'] = $dir;
		}
		return $order;
	}

	public function loadData($objs) {
		$vc = new PartyVoteCache();
		$this->votes = array();
		
		foreach ($objs as $obj) {
			$id = $obj->id;
			$this->data[$id]['id'] = $obj->id;
			$this->data[$id]['party_name'] = $obj->name;
			$this->data[$id]['canEdit'] = Dispatcher::inst()->user->canEditParty($obj);
			
			//[FIXME: shitty code, N queries, but requires few memory and we can omit direct query]
			$has = $vc->getList('', 'WHERE party = '.$obj->id, '', 'LIMIT 1');
			$this->votes[$obj->id] = count($has);
		}
	}

	protected function loadRegions() {
		$region = new Region();
		$regionList = $region->getList('JOIN pol_party_regions pr ON pr.region = t.id', 'ORDER BY t.level ASC, p.name ASC, t.name ASC');
		$regions = array();
		$parents = array();
		foreach ($regionList as $region) {
			if (!$region->parent || @$regionList[$region->parent]->level < 2)
				$regions['root'][] = $region;
			else {
				if (!isset($regions[$region->parent]))
					$parents[] = $regionList[$region->parent];
				$regions[$region->parent][] = $region;
			}
		}

		return array('regions' => $regions, 'parents' => $parents);
	}

	public function show($smarty) {
		$regionInfo = $this->loadRegions();
		$smarty->assign('regions', $regionInfo['regions']);
		$smarty->assign('parents', $regionInfo['parents']);
		if (isset($_GET['region']))
			$smarty->assign('selectedRegion', $_GET['region']);
	
		$smarty->assign('formdata',   $this->data);
		$smarty->assign('votes', $this->votes);
		
		$smarty->assign('formsort', $this->sorting);
		$smarty->display('indexPage.html');
	}
}




?>