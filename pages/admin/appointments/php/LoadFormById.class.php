<?php

require_once('FormPage.class.php');
require_once('Appointment.class.php');

abstract class LoadFormById extends FormPage {
	public function processGet($get) {
		try {
			$this->app = new Appointment($get['id']);
			if (!(Dispatcher::inst()->user->isSuperAdmin() || $this->app->region == $_SESSION['role']->getRecord()->id))
				Dispatcher::forbidden();
		} catch (Exception $e) {
			Dispatcher::forbidden();
		}
		
		$this->politician = $this->app->getPolitician(); //associated politician
		
		parent::processGet($get);
	}
}