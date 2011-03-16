<?php

class RaadsstukSubmitType extends Record {
	protected $data = array(
		'name' => null,
	);

	protected $tableName = 'rs_raadsstukken_submit_type';

	/** Returns all submit types for "Raadsvoorstel" */
	public function getRaadsstukTypes() {
		//[FIXME: this code sucks. this design sucks. ]
		
		$result = array();
		foreach($this->getList($where='WHERE id IN (1, 2, 5)') as $row) {
			$result[$row->id] = $row->name;
		}
		return $result;
	}

	public function getSubmitType($type, $submitters) {
		switch ($type) {
			case 1:
				return $submitters; //College, Presidium, Onbekend
				
			case 2:
			case 3:
			case 4:
				return 3; //Raadslid
				
			case 5:
				return 4; //Burger
		}

		return 5; //Onbekend
	}
}