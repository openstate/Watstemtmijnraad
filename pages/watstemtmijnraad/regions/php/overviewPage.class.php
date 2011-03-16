<?php

require_once('IndexHandler.class.php');
require_once('Region.class.php');
require_once('Level.class.php');
require_once('Raadsstuk.class.php');

/** Shows region information. */
class OverviewPage extends IndexHandler {
	private $region;
	private $voting_count = null;

	public function show($smarty) {
		if($this->region->level == Region::getLevel('Provincie')) {
			$children = $this->region->getActiveChildrenUndeepWithoutRaadsstukCount();
			foreach($children as $kiddo){
				$rs = new Raadsstuk();
				$voting_count[$kiddo->id] = $rs->getCountForLastMonthByRegion($kiddo->id);
			}
		} else {
			$children = $this->region->selectChildren(false, false, true);
		}

        switch($this->region->level) {
            case Region::$LEVELS['Internationaal']:
                $crumbs[0]['title'] = 'Kies een land';
                break;
            case Region::$LEVELS['Landelijk']:
                $crumbs[0]['title'] = 'Kies een provincie';
                break;
            case Region::$LEVELS['Provincie']:
                $crumbs[0]['title'] = 'Kies een regio';
                break;
            default:
                $crumbs[0]['title'] = 'Kies een regio';
                break;
        }
		if(count($children) == 0) {
			$child_level = new Level($this->region->level+1);
		} else {
			$child_level = false;
		}

		$header = $this->region->name;

		if(isset($voting_count))
			$smarty->assign('voting_count', $voting_count);
		$smarty->assign('children', $children);
		$smarty->assign('region', $this->region);
		$smarty->assign('child_level', $child_level);
		$smarty->assign('crumbs', $crumbs);
		$smarty->assign('header', $header);
		parent::show($smarty);
	}

	public function processGet($get) {
		if(!count($get)){
			Dispatcher::header('/');
		}

		try {
			$this->region = new Region(@$get['id']);
		} catch (Exception $e) {
			Dispatcher::notFound();
		}

		if(count($this->region->selectChildren()) < 1) {
			Dispatcher::header('/');
		}
	}
}