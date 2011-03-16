<?php

require_once('TimeRange.class.php');


abstract class createPageBase {
	protected $data;
	protected $errors = array();
	protected $dataLoaded = false;
	protected $politician;
	
	protected $manager;
	protected $error_party = null;
	

	public function __construct() {
		$this->clear();
		$this->errors = array(
			'region_0' => false,
			'party_0' => false,
			'category_0' => false,
			'time_start_0' => false,
			'time_end_0' => false,
			'time_start_1' => false
		);
	}
	
	public function clear() {
		$t = getdate();
		$et = mktime(0,0,0, $t['mon'], $t['mday'], $t['year']+1);
		$this->data = array(
			'id' => false,
			'region' => '',
			'party' => '',
			'category' => '',
			'time_start' => array('unix' => $t[0], 'format' => date("Y-m-d", $t[0])) ,
			'time_end' => array('unix' => $et, 'format' => date("Y-m-d", $et)),
			'description' => ''
		);
	}
	
	public function null() {
		$this->data = array(
			'id' => false,
			'region' => null,
			'party' => null,
			'category' => null,
			'time_start' => null,
			'time_end' => null,
			'description' => null
		);
	}

	public function processPost($post) {
		if(isset($post['cancel'])) Dispatcher::header('/appointments/'.$this->politician->id);
		
		$this->setPost($post);
		if ($this->validate()) { // Success
			$this->dataLoaded = false;
			$this->action();
			return true;
		}
		return false;
	}

	private function setDate($date) {
		$dat = $this->dateArray($date['Date_Day'], $date['Date_Month'], $date['Date_Year']);
		$strdat = implode('-', $dat);
		
		if($strdat == '--' || checkdateArray($dat)) {
			return array(
				'unix' => $strdat == '--'? null: mktime(0, 0, 0, $date['Date_Month'], $date['Date_Day'], $date['Date_Year']),
				'format' => $strdat,
				'parts' => $dat
			);
		}
		else return null;
	}
	
	private function dateArray($day, $month, $year) {
		if(!$day && !$month && !$year) return array('', '', '');
		return array('year' => $year? $year: date('Y'), 'month' => $month? $month: date('m'), 'day' => $day? $day: date('d'));
	}

	public function setPost($post) {
		$this->null();
		// Conversions from post data to actual values
		// For example, checkboxes use $data[] = isset($post[]);

		// Assignments from post data
		if (isset($post['region'])) $this->data['region'] = $post['region'];
		if (isset($post['party'])) $this->data['party'] = $post['party'];
		if (isset($post['category'])) $this->data['category'] = $post['category'];
		if (isset($post['date_start'])) $this->data['time_start'] = $this->setDate($post['date_start']);
		if (isset($post['date_end'])) $this->data['time_end'] = $this->setDate($post['date_end']);
		if (isset($post['description'])) $this->data['description'] = $post['description'];
		$this->dataLoaded = true;
	}

	public function validateReduce($prev, $curr) {
		return $prev || $curr;
	}

	public function validate() {
		$r = new Region();
		if (!(isset($this->data['region']) && ctype_digit($this->data['region']) && $this->data['region'] > 0 && $r->exists($this->data['region']))) $this->errors['region_0'] = true;
		$p = new Party();
		$lp = new LocalParty();
		if (!(isset($this->data['party']) && ctype_digit($this->data['party']) && $this->data['party'] > 0 && $p->exists($this->data['party']))) {
			$this->errors['party_0'] = true;
		} else {
			if (!$this->errors['region_0']) {
				//if ($lp->loadLocalParty($this->data['party'], $this->data['region']) == false) $this->errors['party_0'] = true;
			}
		}
		$c = new Category();
		if (!(isset($this->data['category']) && $c->exists(intval($this->data['category'])))) $this->errors['category_0'] = true;
		if (!isset($this->data['time_start'])) $this->errors['time_start_0'] = true;
		if (!isset($this->data['time_end'])) $this->errors['time_end_0'] = true;
		if (@$this->data['time_start']['format'] != '--' && @$this->data['time_end']['format'] != '--' && compareDateArray($this->data['time_start']['parts'], $this->data['time_end']['parts']) != -1) $this->errors['time_start_1'] = true;
		
		//fetches all the data
		$this->manager = new AppointmentManager($this->politician);
		$this->error_party = $this->manager->addAppointment($this->data['party'], $this->data['category'], intval($this->data['region']), $this->data['time_start']['format'] == '--'? null: $this->data['time_start']['format'] , $this->data['time_end']['format'] == '--'? null: $this->data['time_end']['format']);
		if($this->error_party != null) $this->errors['party_1'] = true;
		
		return !array_reduce($this->errors, array($this, 'validateReduce'), false);
	}
    
	public function show($smarty) {
		$smarty->assign('formdata',   $this->data);
		$smarty->assign('formerrors', $this->errors);
		$smarty->assign('error_party', $this->error_party);
		
		$smarty->display('createPage.html');
	}
}

?>