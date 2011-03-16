<?php

class DatePage {
	protected $post = array();
	protected $error = array();

	public function __construct() {
		if (!array_key_exists('role', $_SESSION) || !($_SESSION['role'] instanceof BOUserRoleClerk))
			Dispatcher::forbidden();
	}

	public function processPost($post) {
		$this->post = $post;
		if (array_key_exists('date', $post)) {
			if (array_key_exists('check', $post)) {
				$date = implode('-', array_reverse(explode('-', $post['date'])));
				if (strtotime($date) !== false) {
					$_SESSION['setup_wizard']['date'] = $date;
					Dispatcher::header('/wizard/party');
				} else
					$this->error['date_invalid'] = true;
			} else
				$this->error['check'] = true;
		} else
			$this->error['date'] = true;
	}

	public function show($smarty) {
		$smarty->assign('post', $this->post);
		$smarty->assign('error', $this->error);
		$smarty->display('date.html');
	}
}
