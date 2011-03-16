<?php

require_once('Raadsstuk.class.php');
require_once('Page.class.php');
require_once('Politician.class.php');
require_once('Region.class.php');

define("RECENT_ITEMS", 4);

class IndexPage {
	private $noFlash;

	public function show($smarty) {
		$p = new Page();
		$r = new Raadsstuk();

		$recent = $r->listRecent(RECENT_ITEMS, Dispatcher::inst()->region, true);

		$bar_width = array();
		foreach($recent as $voting) {
			$contra = $voting->vote_1;
			$total = $voting->vote_0 + ($voting->vote_1); //Don't count 'not voted'

			if(!$total == 0)
				$bar_width[$voting->id] = (($contra / $total) * 100)-4; //magical number -4 has to do with padding
			else //This should never happen. If it does anyway, make it 50-50
				$bar_width[$voting->id] = 46; //50-4, magical number -4 has to do with padding
		}

		$smarty->assign('bar_width', $bar_width);
		//$smarty->assign('municipalities', Region::getDropDownRegionsByLevel('Gemeente'));
		$smarty->assign('provinces', Region::getDropDownRegionsAllByLevel('Provincie'));
		$smarty->assign('recent', $recent);
		$smarty->assign('noflash', $this->noFlash);
		$smarty->assign('page', $p->loadByUrl('home', Dispatcher::inst()->region));
		$smarty->display('indexPage.html');
	}

	public function processGet($get) {
		if(!count($get)){
			$this->noFlash = false;
		}

		if(isset($get['noflash']) && $get['noflash'] == 1) {
			$this->noFlash = true;
		} else {
			$this->noFlash = false;
		}
	}
}

?>