<?php

require_once('Politician.class.php');

class ProfilePage {
	private $politician;
	private $appointments;
	private $app_parties;
    private $role;

	public function processGet($get){
        $this->role = $_SESSION['role']->getRecord()->id;
		if(isset($get['id'])){
			$this->politician = new Politician($get['id']);
		} else {
			Dispatcher::notFound();
		}

		$this->appointments = $this->politician->listAllAppointments();
		foreach($this->appointments as $id => $app){
			$this->app_parties[$id] = new Party($app->party);
		}
	}

	public function show($smarty) {
        $smarty->assign('role', $this->role);
		$smarty->assign('politician', $this->politician);
		$smarty->assign('appointments', $this->appointments);
		$smarty->assign('app_parties', $this->app_parties);
		$smarty->display('politician_profile.html');
	}

}