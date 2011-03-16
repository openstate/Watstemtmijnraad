<?php

require_once('FormHandler.class.php');
require_once('GettextPOModule.class.php');
require_once('Message.class.php');
require_once('Category.class.php');
require_once('Politician.class.php');
require_once('LocalParty.class.php');
require_once('Vote.class.php');

abstract class FormPage extends FormHandler {
	protected $pofile;
	protected $record;
	//protected $localparty;
	protected $politician;
	protected $app;
	protected $error_party = null;
	
	protected $go_back_party = null;
	protected $commit = null;
	protected $votes;
	

	public function __construct() {
		$this->data = array(
			'politician' => null,
			'category' => null,
			'category_name' => null,
			'time_start_array' => null,
			'time_start' => null,
			'time_end_array' => null,
			'time_end' => null,
			'description' => null,
		);

		$this->errors = array(
			'politician_required' => false,
			'category_invalid' => false,
			'time_start_invalid' => false,
			'time_end_invalid' => false,
			'time_negative' => false,
			'votes_deleted' => false,
			'party_overlap' => false,
		);
	}

	public function processGet($get) {
		if (!Dispatcher::inst()->user->isSuperAdmin()) {
			//$this->localparty = @$_SESSION['roleCache']['localparty'];
			$this->go_back_party = isset($get['localparty'])? intval($get['localparty']): null; //used to go back to party view
		}

		$this->loadData($this->getRecord());
	}

	public function processPost($post) {
		if(isset($post['cancel'])) {
			if($this->go_back_party !== null) {
				Dispatcher::header('/appointments/region/'.$this->region->id);
			} else {
				Dispatcher::header('/appointments/region/'.$this->region->id);
			}
		}
		
		parent::processPost($post);
	}
	
	protected function getRecord() {
		return $this->app;
	}

	protected function loadData($record) {
		$this->data['politician'] = $record->politician;
		$this->data['category'] = $record->category;
		$this->data['category_name'] = $record->cat_name;
		$this->data['time_start'] = $record->time_start == NEG_INFINITY ? '--' : $record->time_start;
		$this->data['time_end'] = $record->time_end == POS_INFINITY ? '--' : $record->time_end;
		$this->data['description'] = $record->description;
	}

	//[spagetty logic: validation requires $thi->politician, set in post]
	protected function assign($post) {
		$this->data['politician'] = @$post['politician'];
		$this->data['category'] = @$post['category'];
		$this->data['time_start_array'] = $this->dateArray(@$post['TS_Day'], @$post['TS_Month'], @$post['TS_Year']);
		$this->data['time_start'] = implode('-', $this->data['time_start_array']);
		$this->data['time_end_array'] = $this->dateArray(@$post['TE_Day'], @$post['TE_Month'], @$post['TE_Year']);
		$this->data['time_end'] = implode('-', $this->data['time_end_array']);
		$this->data['description'] = @trim($post['description']);
		$this->commit = isset($post['commit']);
	}

	private function dateArray($day, $month, $year) {
		if(!$day && !$month && !$year) return array('', '', '');
		return array('year' => $year? $year: date('Y'), 'month' => $month? $month: date('m'), 'day' => $day? $day: date('d'));
	}

	//[spagetty logic: validation requires $thi->politician, set in post]
	protected function validate() {
		if (!ctype_digit($this->data['category']) && $this->data['category'] != '-1')	$this->errors['category_invalid'] = true;
		if ($this->data['time_start'] != '--' && !checkdateArray($this->data['time_start_array'])) $this->errors['time_start_invalid'] = true;
		if ($this->data['time_end'] != '--' && !checkdateArray($this->data['time_end_array'])) $this->errors['time_end_invalid'] = true;
		if ($this->data['time_start'] != '--' && $this->data['time_end'] != '--' && compareDateArray($this->data['time_start_array'], $this->data['time_end_array']) > 0) $this->errors['time_negative'] = true;
		
		$start = $this->data['time_start'] == '--' ? NEG_INFINITY : $this->data['time_start'];
		$end = $this->data['time_end'] == '--' ? POS_INFINITY : $this->data['time_end'];
		if(!$this->errors['time_start_invalid'] && !$this->errors['time_end_invalid'] && !$this->errors['time_negative']) {
			$this->checkRanges();
			if(!$this->errors['party_overlap']) {
				$this->votes = Vote::countObsolete($this->getRecord()->politician, $this->getRecord()->id, $start, $end);
				if(!$this->commit && $this->votes > 0) $this->errors['votes_deleted'] = true;
			}
		}
		
		return parent::validate();
	}
	
	protected function checkRanges() {
		$start = $this->data['time_start'] == '--' ? null: $this->data['time_start'];
		$end = $this->data['time_end'] == '--' ? null: $this->data['time_end'];
		
		$this->manager = new AppointmentManager($this->politician);
		$this->error_party = $this->manager->updateAppointment($this->app, $this->data['category'], $this->app->region, $start, $end);
		
		if($this->error_party != null) $this->errors['party_overlap'] = true;
	}
	

	protected function save(Record $r) {
		$this->manager->playChanges($this->data['description']);
		
		$r->category = $this->data['category'];
		$r->time_start = $this->data['time_start'] == '--' ? NEG_INFINITY : $this->data['time_start'];
		$r->time_end = $this->data['time_end'] == '--' ? POS_INFINITY : $this->data['time_end'];
		$r->description = $this->data['description'];
		$r->save();
	}

	private function getPOFile() {
		if (null == $this->pofile)
			$this->pofile = new GettextPOModule('index.po');
		return $this->pofile;
	}

	//[spagetty logic: action requires $this->politician]
	protected function action() {
		$this->addMessage(Message::SUCCESS, 'success');
		if($this->go_back_party !== null) {
			Dispatcher::header('/appointments/party/'.$this->go_back_party);
		} else {
			Dispatcher::header('/politicians/profile/'.$this->politician->id);
		}
	}

	protected function error($e) {
		$this->addMessage(Message::ERROR, 'error');
	}

	private function addMessage($mtype, $type) {
		MessageQueue::addMessage(new Message($mtype, sprintf($this->getPOFile()->getMsgStr('index.'.$type),
																												 $this->getPOFile()->getMsgStr('index.action.'.$this->getAction()))));
	}

	abstract protected function getAction();

	public function show($smarty) {
		$c = new Category();
		
		parent::show($smarty);
		$smarty->assign('categories', $c->getDropdownCategoriesAll());
		$smarty->assign('error_party', $this->error_party);
		$smarty->assign('lost_votes', $this->votes);
		$smarty->display('formPage.html');
	}
}

?>
