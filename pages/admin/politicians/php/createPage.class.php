<?php

require_once('FormPage.class.php');
require_once('Politician.class.php');

class CreatePage extends FormPage {
	protected function getRecord() {
		return new Politician();
	}

	protected function save(Record $r) {
		$r->region_created = isset($_SESSION['role'])? $_SESSION['role']->getRecord()->id: null;
		parent::save($r);
	}

	protected function getFormParameters() {
		return array('name' => 'PoliticianCreate',
								 'header' => 'Nieuwe politicus',
								 'submitText' => 'Toevoegen',
		);
	}

	protected function getAction() {
		return 'create';
	}
}

?>