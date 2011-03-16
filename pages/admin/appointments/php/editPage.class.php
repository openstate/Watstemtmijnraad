<?php

require_once('LoadFormById.class.php');

class EditPage extends LoadFormById {
	protected function getFormParameters() {
		return array('name' => 'AppointmentEdit',
								 'header' => 'Aanstelling wijzigen',
								 'submitText' => $this->votes > 0? 'Toch wijzigen': 'Wijzigen',
								 'showPolitician' => false);
	}

	protected function getAction() {
		return 'edit';
	}
}

?>
