<?php

require_once('LoadFormById.class.php');
require_once('Appointment.class.php');
require_once('Vote.class.php');

class DeletePage extends LoadFormById {
	public function processGet($get) {
		parent::processGet($get);
		if (!Dispatcher::inst()->user->isSuperAdmin()) {
			$app = new Appointment();
			if (count($app->loadByPolitician($this->getRecord()->id)) != 0 || $this->getRecord()->region_created != $_SESSION['role']->getRecord()->id)
				Dispatcher::forbidden();
		}
	}

	public function processPost($post) {
		try {
			$this->getRecord()->delete();
		} catch (Exception $e) {
			$this->error($e);
			return;
		}
		$this->action();
	}

	protected function getFormParameters() {
		return array('name' => 'PoliticianDelete',
		             'header' => 'Politicus verwijderen',
								 'note' => 'Weet u zeker dat u de onderstaande politicus wilt verwijderen?',
								 'submitText' => 'Verwijderen',
								 'freeze' => true);
	}

	protected function getAction() {
		return 'delete';
	}
	
	public function show($smarty) {
		$app = new Appointment();
		$apps = $app->loadByPolitician($this->getRecord()->id);
		$votes = Vote::countVotes($this->getRecord()->id);
		
		$smarty->assign('confirm_deleting', true);
		$smarty->assign('del_votes_count', $votes);
		$smarty->assign('del_appointments', $apps);
		parent::show($smarty);
	}
}

?>