<?php

require_once('Party.class.php');
require_once('LocalParty.class.php');
require_once('Appointment.class.php');

class RegionUpdateHelper {
	private $bound = false;
	
	private $region;
	
	public function __construct($regionid) {
		$this->region = $regionid;
	}
	
	public function getFormParameters($name) {
		return array(
			'header' => "Wilt u de datum waarop '".$name."' wordt gekoppeld aan '". $_SESSION['role']->getRecord()->formatName() ."' wijzigen?",
			'submitText' => 'Wijzigen',
			'date_field' => 'Aanvangsdatum',
			'boundary' => 'Niet later dan'
		);
	}

	public function save(Party $r, $date) {
		$lp = new LocalParty();
		$lp = $lp->loadLocalParty($r->id, $_SESSION['role']->getRecord()->id);
		$lp->time_start = $date == null? '-infinity': $date;
		$lp->save();
	}

	public function getAction() {
		return 'edit';
	}
	
	public function getBoundary($lp) {
		if($this->bound === false) $this->bound = $lp->getStartBoundary();
		/*
		$funcs = Appointment::listByParty($lp->party, $lp->region);
		$bound = null;
		foreach ($funcs as $fun) {
			if($bound == null || $fun->time_start == '-infinity' || ($bound != '-infinity' && strcmp($fun->time_start, $bound) < 0))
				$bound = $fun->time_start;
		}*/
		
		return $this->bound; //no boundary for new objects
	}
	
	
	public function validate($lp, $date) {
		$bound = $this->getBoundary($lp);
		return !($bound !== null && ($bound == '-infinity' || strcmp($bound, $date) < 0));
	}
}

?>