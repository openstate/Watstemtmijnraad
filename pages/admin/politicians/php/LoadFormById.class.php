<?php

require_once('FormPage.class.php');
require_once('Politician.class.php');

abstract class LoadFormById extends FormPage {
	protected $p;

	public function processGet($get) {
		try {
			$this->p = new Politician($get['id']);
			$this->loadData($this->p);
		} catch (Exception $e) {
			Dispatcher::forbidden();
		}
	}

	private function loadData($record) {
		$this->data['title'] = $record->title;
		$this->data['first_name'] = $record->first_name;
		$this->data['last_name'] = $record->last_name;
		$this->data['gender'] = $record->gender_is_male;
		$this->data['email'] = $record->email;
		$this->data['extern_id'] = $record->extern_id;

		$this->data['opensocial_ids'] = array();
		$this->data['opensocial_names'] = array();
		
		$db = DBs::inst(DBs::SYSTEM);
		$res = $db->query('SELECT site_name, opensocial_id FROM pol_politicians_opensocial WHERE politician = %i', $record->id)->fetchAllRows();
		foreach ($res as $row) {
			$this->data['opensocial_ids'][] = $row['opensocial_id'];
			$this->data['opensocial_names'][] = $row['site_name'];
		}
	}

	protected function getRecord() {
		return $this->p;
	}
}

?>
