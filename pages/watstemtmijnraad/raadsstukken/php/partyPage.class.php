<?php

require_once('IndexHandler.class.php');
require_once('Raadsstuk.class.php');
require_once('Party.class.php');
require_once('Vote.class.php');

class PartyPage extends IndexHandler {
	private $rs;

	public function __construct() {
		$this->sortDefault = 'name_sortkey';
		$this->sortKeys = array('id', 'first_name', 'name_sortkey');
	}

	public function processGet($get) {
		try {
			$this->rs = new Raadsstuk(@$get['raadsstuk']);

			$ids = Dispatcher::sessionUser() ? Dispatcher::sessionUser()->listSiteIds(): User::listDefaultSiteIds();
			if(!isset($ids[$this->rs->site_id])) Dispatcher::forbidden();

			$this->party = new Party(@$get['id']);
			$this->data = $this->rs->listVotesOfParty($this->party, $this->getOrder()); //throws not-voted
		} catch (Exception $e) {
			Dispatcher::header('/');
		}
	}

	public function show($smarty) {
		$smarty->assign('raadsstuk', $this->rs);
		$smarty->assign('party', $this->party);
		parent::show($smarty);
	}
}
?>
