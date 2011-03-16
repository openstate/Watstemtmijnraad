<?php

require_once('LoadFormById.class.php');

class DeletePage extends LoadFormById {

    protected $contain_errors = false;

	public function processPost($post) {
        if(isset($post['cancel']))
            Dispatcher::header('/appointments');
		try {
			$this->getRecord()->delete();
		} catch (Exception $e) { //fails if there is a vote record
			$this->contain_errors = true;
			return;
		}
		$this->action();
	}

	protected function getFormParameters() {
		return array('name' => 'PartyDelete',
		             'header' => 'Partij verwijderen',
								 'note' => 'Weet u zeker dat u de onderstaande partij wilt verwijderen?',
								 'submitText' => 'Verwijderen',
								 'freeze' => true,
                                 'errors' => $this->contain_errors);
	}

	protected function getAction() {
        var_dump(debug_backtrace());
		return 'delete';
	}
}

?>
