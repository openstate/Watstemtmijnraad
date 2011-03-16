<?php

require_once('FormPage.class.php');
require_once('Page.class.php');

class CreatePage extends FormPage {
	public function __construct() {
		$this->page = new Page();
	}

	protected function loadData($record) {
		if(!isset($_SESSION['role'])) Dispatcher::header('/login');
		
		parent::loadData($record);
		$this->data['showInMenu'] = 1;
		$this->data['region'] = $_SESSION['role']->getRecord()->id;
	}

	protected function getFormParameters() {
		return array('name' => 'PageCreate',
								 'header' => 'Nieuwe pagina',
								 'submitText' => 'Toevoegen');
	}

	protected function validate() {
		$p = new Page();
		if ($p->exists(@$this->data['url'], (int)$_SESSION['role']->getRecord()->id)) $this->errors['url_exists'] = true;
		return parent::validate();
	}

	protected function getAction() {
		return 'create';
	}
}