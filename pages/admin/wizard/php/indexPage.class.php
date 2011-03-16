<?php

class IndexPage {
	public function __construct() {
		if (!array_key_exists('role', $_SESSION) || !($_SESSION['role'] instanceof BOUserRoleClerk))
			Dispatcher::forbidden();
		Dispatcher::header('/wizard/date');
	}
}

?>