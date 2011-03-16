<?php

require_once('LocalParty.class.php');
require_once('Region.class.php');
require_once('Party.class.php');
require_once('BackofficeUser.class.php');
require_once('Message.class.php');
require_once('Appointment.class.php');
require_once('TimeRange.class.php');


class regionsPage {
	protected $id = null;
	protected $edit = null;
	private $message = '';
	private $formdata = null;

	private $time_ranges = array();
	private $parties = null;
	private $errors = array();
	private $constrained = array();


	public function processGet($get) {
		//only super admin (not restricted) or Griffi (restricted on regoin) are allowed
		if (!Dispatcher::inst()->user->isSuperAdmin() && (!isset($_SESSION['role']) || !($_SESSION['role']->getRecord() instanceof Region))) Dispatcher::forbidden();

		//if party id is not given
		if(!isset($get['id']) || !ctype_digit($get['id'])) Dispatcher::header('../');
		$this->id = $get['id'];

		if(isset($get['edit']) && ctype_digit((string)$get['edit'])) {
			try {
				$this->edit = new LocalParty();
				$this->edit->load(intval($get['edit']));

				if(!Dispatcher::inst()->user->isSuperAdmin() && $this->edit->region != $_SESSION['role']->getRecord()->id) $this->edit = null; //not allowed to edit
			} catch (Exception $e) {
				Dispatcher::notFound();
			}
		}

		//build ranges
		$pr = new LocalParty();
		$this->parties = $pr->getList('', 'WHERE party = '.intval($this->id).(!Dispatcher::inst()->user->isSuperAdmin()? ' AND region = '.intval($_SESSION['role']->getRecord()->id): ''), 'ORDER BY p.name, t.time_start');
		foreach ($this->parties as $party) {
			if(!isset($this->time_ranges[$party->region])) $this->time_ranges[$party->region] = new TimeRange();
			list($start, $end) = TimeRange::postgresTimes($party->time_start, $party->time_end);
			$this->time_ranges[$party->region]->addRange($party->id, $start, $end);
		}

		//fix old inconsistent data
		/*if(!isset($_GET['fixed'])) {
			if($this->playChanges()) Dispatcher::header("/party/regions/{$this->id}?fixed=true");
		} else { //something bad in TimeRange, we can't get our state fixed, software bug detected
			trigger_error("Software bug detected, TimeRange is unable to fix inconsistent ranges: {$this->id}", E_USER_WARNING); //hopelly this goes to log
			Dispatcher::badRequest();
		}*/
	}

	public function processPost($post) {
		if(isset($post['delete'])) {
			if(!isset($post['prs'])) return;

			try {
				foreach($post['prs'] as $id) {
					$pr = new LocalParty();
					$pr->load($id);
					//$pr->delete();

					if(isset($this->time_ranges[$pr->region])) { //otherwise ignore wrong id's
						$this->time_ranges[$pr->region]->deleteRange($id);
					} else trigger_error("Range not found, id: {$id}", E_USER_WARNING);
				}
			} catch (Exception $e) {
				//[FIXME: if TimeRange throws exception, then comment out badRequest() and allow it pass once.
				// it will fix all the ranges, after that you will never get that error again (unless you touch the ranges
				// manually again). ]
				trigger_error("Unexpected exception by party region regisration deletion: " . $e->getMessage(), E_USER_WARNING);
				Dispatcher::badRequest();
			}
            
			$apps = Appointment::listByParty($this->id, (!Dispatcher::inst()->user->isSuperAdmin()? $_SESSION['role']->getRecord()->id: null));
			foreach ($apps as $ap) {
				if(!isset($this->time_ranges[$ap->region])) $this->time_ranges[$ap->region] = new TimeRange(); //warning: fix range
				list($start, $end) = TimeRange::postgresTimes($ap->time_start, $ap->time_end);
				if($this->time_ranges[$ap->region]->addContentRange($start, $end)) $this->constrained[] = $ap;
			}

			if(empty($this->constrained)) { //apply changes
				$this->playChanges();
				$this->addMessage(Message::SUCCESS, 'Regio verwijderd.');
				Dispatcher::header('/party/regions/' . $this->id);
			} else {
				$this->formdata = $post;
			}
			return;
		} elseif(isset($post['add']) || isset($post['edit']) || isset($post['commit'])) { //commit -- always edit
			$post['time_start'] = $time_start = @$post['TS_Year'].'-'.@$post['TS_Month'].'-'.@$post['TS_Day'];
			$time_start_correct = $time_start == '--'? null: ((isset($post['TS_Year']) && $post['TS_Year'] != ''? $post['TS_Year']: date('Y')).'-'.(isset($post['TS_Month']) && $post['TS_Month'] != ''? $post['TS_Month']: date('m')).'-'.(isset($post['TS_Day']) && $post['TS_Day'] != ''? $post['TS_Day']: date('d')));
			$post['time_end'] = $time_end = @$post['TE_Year'].'-'.@$post['TE_Month'].'-'.@$post['TE_Day'];
			$time_end_correct = $time_end == '--'? null: ((isset($post['TE_Year']) && $post['TE_Year'] != ''? $post['TE_Year']: date('Y')).'-'.(isset($post['TE_Month']) && $post['TE_Month'] != ''? $post['TE_Month']: date('m')).'-'.(isset($post['TE_Day']) && $post['TE_Day'] != ''? $post['TE_Day']: date('d')));

			if($time_start_correct != null && !checkdate($post['TS_Month'], $post['TS_Day'], $post['TS_Year'])) {
				$this->errors['time_start_invalid'] = true;
				$this->formdata = $post;
				return;
			}

			if($time_end_correct != null && !checkdate($post['TE_Month'], $post['TE_Day'], $post['TE_Year'])) {
				$this->errors['time_end_invalid'] = true;
				$this->formdata = $post;
				return;
			}

			//bound to region
			if(!Dispatcher::inst()->user->isSuperAdmin()) $region = $_SESSION['role']->getRecord()->id;
			elseif(!isset($post['region']) || $post['region'] == '') {
				$this->errors['region_missing'] = true;
				$this->formdata = $post;
				return;
			} else $region = intval($post['region']);


			if(!isset($this->time_ranges[$region])) $this->time_ranges[$region] = new TimeRange();
			try {
				//add or edit button
				if((isset($post['edit']) || isset($post['commit'])) && isset($post['id'])) $this->time_ranges[$region]->updateRange($post['id'], $time_start_correct, $time_end_correct);
				elseif(isset($post['add'])) $this->time_ranges[$region]->addRange(null, $time_start_correct, $time_end_correct);
			} catch (InvalidArgumentException $e) {
				$this->errors['range_error'] = true;
				$this->formdata = $post;
				return ;
			}

			//check excluded appointments
			$apps = Appointment::listByParty($this->id, (!Dispatcher::inst()->user->isSuperAdmin()? $region: null));
			foreach ($apps as $ap) {
				if(!isset($this->time_ranges[$ap->region])) $this->time_ranges[$ap->region] = new TimeRange(); //warning: fix range
				list($start, $end) = TimeRange::postgresTimes($ap->time_start, $ap->time_end);
				if($this->time_ranges[$ap->region]->addContentRange($start, $end)) $this->constrained[] = $ap;
			}

			if(empty($this->constrained) || isset($post['commit'])) { //apply changes
				$this->playChanges();
				Dispatcher::header('/party/regions/' . $this->id);
			} else {
				$this->formdata = $post;
			}
		}
	}

	protected function addMessage($mtype, $message) {
		MessageQueue::addMessage(new Message($mtype, $message));
	}

	public function show($smarty) {
		if($this->formdata != null) $smarty->assign('formdata', $this->formdata);

		$smarty->assign('regions', Region::getDropDownRegionsAll());
		$smarty->assign('constrained', $this->constrained);
		$smarty->assign('errors', $this->errors);
		$smarty->assign('superAdmin', Dispatcher::inst()->user->isSuperAdmin());
		$smarty->assign('bound_region', isset($_SESSION['role'])? $_SESSION['role']->getRecord()->formatName(): null);
		if($this->message != '') $smarty->assign('message', $this->message);

		if($this->edit !== null) {
			$smarty->assign('id', $this->id);
			list($start, $end) = TimeRange::postgresTimes($this->edit->time_start, $this->edit->time_end);
			if(empty($this->formdata)) $smarty->assign('formdata', array(
				'id' => $this->edit->id,
				'region' => $this->edit->region,
				'time_start' => !$start? '--' : $start,
				'time_end' => !$end? '--': $end
			));

			$smarty->display('regionsEditPage.html');

			die(); //show edit form
		}

		$party = new Party();
		$party->load($this->id);
		$smarty->assign('party', $party);

		$smarty->assign('prs', $this->parties);


		$smarty->display('regionsPage.html');
	}



	private function playChanges() {
		$changed = false;
		$patch = array();
		foreach ($this->time_ranges as $region => $rng) { //commit changes
			$changes = $rng->playChanges();
			foreach($changes as $chg) {
				$changed = true;
				$pr = new LocalParty();
				if($chg['id'] > 0) $pr->load($chg['id']);
				if($chg['action'] == 'delete') {
					$pr->delete();
					$this->addMessage(Message::SUCCESS, "Party region range deleted: {$chg['id']}");
				} else {
					$pr->party = intval($this->id);
					$pr->region = $region;
					$pr->time_start = $chg['start']? $chg['start']: '-infinity';
					$pr->time_end = $chg['end']? $chg['end']: 'infinity';
					$pr->save();
					$patch[$chg['id']] = $pr->id;
					$this->addMessage(Message::SUCCESS, "Party region range changed: {$chg['id']}, range: {$chg['start']} to {$chg['end']}");
				}
			}
			$rng->clearChanges($patch);
		}
		return $changed;
	}
}

?>