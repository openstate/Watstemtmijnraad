<?php
require_once('Region.class.php');
require_once('Raadsstuk.class.php');

define('MAX_RAADSSTUKKEN_BY_REGION', 10);

/**
* Displays raadsstukken at specific level and all sublevels.
* 
* Note: regions are not build using nested-set trees, this operation
* is inefficient.
* 
* @author Sardar Yumatov (ja.doma@gmail.com)
*/
class RegionPage {
	private $raadsstukken;
	private $region;
	private $theme;
    
	public function processGet($get) {
		if(!isset($get['id'])) Dispatcher::notFound();
		$limit = isset($get['limit'])? min(MAX_RAADSSTUKKEN_BY_REGION, intval($get['limit'])): MAX_RAADSSTUKKEN_BY_REGION;
		if($limit < 1) $limit = 1;
		
		//lookup for region
		$this->region = new Region();
		try {
			$this->region->load(intval($get['id']));
		} catch (Exception $e) {
			Dispatcher::notFound();
		}

        if($get['theme']) {
            if($get['theme'] == 'dark') {
                $this->theme = 'dark';
            } else if ($get['theme'] == 'light') {
                $this->theme = 'light';
            } else {
                $this->theme = 'dark';
            }
        }

		//lookup for the raadsstukken
		$r = new Raadsstuk();
		$this->raadsstukken = $r->listRecentByRegion($limit, intval($get['id']));
	}
	
	public function show($smarty) {
		$smarty->assign('raadsstukken', $this->raadsstukken);
		$smarty->assign('num', sizeof($this->raadsstukken));
		$smarty->assign('region', $this->region);
		
		$smarty->assign('theme', $this->theme); //'dark' or 'light'
		//TODO: color choice!
		
		$smarty->setBaseTemplate('../templates/small_iframe.html');
		$smarty->display('regionPage.html');
	}
}

?>