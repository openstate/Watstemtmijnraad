<?php

require_once('createPageBase.class.php');


class createPage extends createPageBase {
	public function saveToObject() {
		require_once('BackofficeUser.class.php');
		$obj = new BackofficeUser();
		$this->doSaveToObject($obj);
		$obj->save();
	}

	public function show($smarty) {
		parent::show($smarty);
	}

	public function action() {
		try {
			$this->saveToObject();
		} catch (Exception $e) {
			$this->errors['username_1'] = true;
			return false;
		}
		Dispatcher::header('../');
	}
}

?>