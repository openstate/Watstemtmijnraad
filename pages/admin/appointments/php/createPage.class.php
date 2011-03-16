<?php

require_once('Party.class.php');
require_once('Category.class.php');
require_once('createPageBase.class.php');
require_once('Politician.class.php');
require_once('Appointment.class.php');

class createPage extends createPageBase {
	public function processGet($get) {
		try {
			$this->politician = new Politician();
			$this->politician->load(@$get['politician']);
		} catch (Exception $e) {
			Dispatcher::header('/appointments/');
		}
	}

	/*public function saveToObject() {
		require_once('Appointment.class.php');
		$obj = new Appointment();
		$this->doSaveToObject($obj);
		//$obj->save();
	}*/

	public function show($smarty) {
		$p = new Party();
		$c = new Category();
		$smarty->assign('regions', Region::getDropDownRegionsAll());
		$smarty->assign('parties', Party::getDropDownPartiesAll());
		$smarty->assign('categories', Category::getDropDownCategoriesAll());
		$smarty->assign('politician', $this->politician);
		parent::show($smarty);
	}

	public function action() {
		//$this->saveToObject();
		$this->manager->playChanges($this->data['description']);
		Dispatcher::header('/appointments/'.$this->politician->id);
	}
}

?>
