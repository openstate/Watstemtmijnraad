<?php

require_once('Page.class.php');
require_once('Region.class.php');

class indexPage {
	private $region;

	public function processGet($get) {
		if (!isset($get['id'])) Dispatcher::header('/');

		$p = new Page();
		$r = isset($get['region'])? intval($get['region']): @Dispatcher::inst()->region->id;
		$this->page = $p->loadByUrl($get['id'], $r);
		$this->region = $r;
	}

	public function show($smarty) {
		$reg = new Region($this->region);

		$crumbs[0]['url'] = '/regions/region/'.$reg->id;
		$crumbs[0]['title'] = $reg->formatName();
		$crumbs[1]['title'] = $this->page->title;

		$smarty->assign('dbpage', $this->page);
		$smarty->assign('crumbs', $crumbs);
		$smarty->display('indexPage.html');
	}
}

?>