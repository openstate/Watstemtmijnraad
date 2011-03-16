<?php

require_once('indexPageBase.class.php');

class indexPage extends indexPageBase {
	protected function loginCheck() {
		return Dispatcher::inst()->user->login($this->data['username'], $this->data['password'], $this->data['cookie']);
	}

	public function show($smarty) { //Destination is not really used anymore...
		if (isset($_REQUEST['destination'])) {
			$destination = $_REQUEST['destination'];
		} else if (isset($_SERVER['HTTP_REFERER'])) {
			$destination = $_SERVER['HTTP_REFERER'];
		} else {
			$destination = '/';
		}
		$this->data['destination'] = $destination;
		parent::show($smarty);
	}

	public function action() {
		Dispatcher::header('/');
	}
}

?>