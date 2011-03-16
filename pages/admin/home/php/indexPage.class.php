<?php

require_once('Politician.class.php');
require_once('LocalParty.class.php');
require_once('Region.class.php');

require_once('BOUserRolePolitician.class.php');
require_once('BOUserRoleSecretary.class.php');
require_once('BOUserRoleClerk.class.php');

class indexPage {
	private $loggedin = false;

	public function processGet($get) {
		if (Dispatcher::inst()->user->loggedIn) {
			$this->selectPage();
			$this->loggedin = true;
		} else {
            Dispatcher::inst()->header('/login');
        }
	}

	public function selectPage() {
		$dispatch = Dispatcher::inst();

		$politician = new Politician();
		$politicianList = $politician->loadByBoUser($dispatch->user->id);
		$politicianCount = count($politicianList);

		$secretaryList = $dispatch->user->listAllowedParties();
		$secretaryCount = count($secretaryList);

		$regionList = $dispatch->user->listAllowedRegions();
		$regionCount = count($regionList);

		if (($politicianCount == 1) && ($secretaryCount == 0) && ($regionCount == 0)) {
			$_SESSION['role'] = new BOUserRolePolitician(array_pop(array_keys($politicianList)));
			$dispatch->header('/questions');
		} elseif (($politicianCount == 0) && ($secretaryCount == 1) && ($regionCount == 0)) {
			$_SESSION['role'] = new BOUserRoleSecretary(array_pop(array_keys($secretaryList)));
			$dispatch->header('/appointments');
		} elseif (($politicianCount == 0) && ($secretaryCount == 0) && ($regionCount == 1)) {
			$_SESSION['role'] = new BOUserRoleClerk(array_pop(array_keys($regionList)));
			$dispatch->header($_SESSION['role']->getRecord()->used_wizard ? '/raadsstukken/' : '/wizard/');
		} else {
			$sum = $politicianCount + $secretaryCount + $regionCount;
			if ($sum == 0) {
				if (!$dispatch->user->isSuperAdmin()) {
					$dispatch->header('/selection');
				}
			} else {
				$dispatch->header('/selection');
			}
		}
	}

	public function show($smarty) {
		$smarty->assign('loggedin', $this->loggedin);
		$smarty->display('indexPage.html');
	}
}

?>
