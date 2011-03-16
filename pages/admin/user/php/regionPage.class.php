<?php
require_once('Region.class.php');
require_once('Level.class.php');
require_once('BackofficeUser.class.php');

class regionPage {
	protected $user;
	private $regions = null;

	public function processGet($get) {
		try {
			if(!isset($get['id']) || !ctype_digit($get['id'])) Dispatcher::badRequest();
			$this->user = new BackofficeUser();
			$this->user->load(intval($get['id']));
		} catch (Exception $e) {
			Dispatcher::notFound();
		}
	}

	public function processPost($post) {
		try {
			$this->user->setAllowedRegions(@$post['regions']);
		} catch (Exception $e) {
			Dispatcher::badRequest();
		}

		Dispatcher::header('/user/region/'.$this->user->id);
	}

	public function show($smarty) {
		$smarty->assign('levels', Level::listListOrdered());
		$smarty->assign('regions', Region::listInDepthRegions());
		$smarty->assign('selectedRegions', $this->user->listAllowedRegions());

		$smarty->assign('user', $this->user);
		$smarty->display('regionPage.html');
	}
}

?>