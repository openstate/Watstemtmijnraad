<?php

require_once('LoadFormById.class.php');

class DeletePage extends LoadFormById {
	public function processPost($post) {

		if(!isset($post['cancel'])) {
			try {
				$this->getRecord()->delete();
			} catch (Exception $e) {
				$this->error($e);
				return;
			}
		}
		$this->action();
	}

	protected function getFormParameters() {
		return array('name' => 'RaadsstukDelete',
					 			 'header' => 'Raadsstuk verwijderen',
								 'note' => 'Weet u zeker dat u het onderstaande raadsstuk wilt verwijderen?',
								 'submitText' => 'Verwijderen',
								 'freeze' => true);
	}

	protected function getAction() {
		return 'delete';
	}

	public function show($smarty) {
		$smarty->assign('preview_link', 'http://'.(isset($_SESSION['role'])? $_SESSION['role']->getRecord()->subdomain.'.':'').Dispatcher::inst()->domain.'.'.Dispatcher::inst()->tld.'/raadsstukken/raadsstuk/'.$this->rs->id);
		parent::show($smarty);
	}
}

?>
