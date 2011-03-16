<?php

require_once('FormPage.class.php');
require_once('Appointment.class.php');

class CreatePartyPage extends FormPage {

	private $localparty;

	public function processGet($get) {
		if (isset($get['localparty']) && ctype_digit((string)$get['localparty'])){
			$id = $get['localparty'];
		} elseif (isset($get['id']) && ctype_digit((string)$get['id'])){
			$id = $get['id'];
		} else {
			~Dispatcher::badRequest();
		}

		try {
			$this->localparty = new LocalParty();
			$this->localparty->load($id);
		} catch (Exception $e) {
			Dispatcher::notFound();
		}

		//[weird logic: this allows extending from FormPage (as if we editing app, which is currently dummy)]
	  	$this->app = new Appointment();
	  	//[weird logic: dummy appointment, used only to provide 'politician' id for option.select(current) ]
	  	if (@ctype_digit($get['politician'])) $this->app->politician = $get['politician'];

	  	//next year
		$this->app->time_end = date('Y-m-d', strtotime('+1 year'));

		parent::processGet($get);
	}

	protected function getFormParameters() {
		return array('name' => 'AppointmentCreate',
								 'header' => 'Nieuwe aanstelling',
								 'submitText' => 'Toevoegen',
								 'showPolitician' => true);
	}

	protected function getAction() {
		return 'create';
	}

	protected function validate() {
		//we have to provide correct $this->politician to FormPage in order to
		//save the new appointment (used for range fetches).
		try {
			$this->politician = new Politician();
			$this->politician->load(intval($this->data['politician']));
		} catch (Exception $e) {
			$this->errors['politician_required'] = true;
			return false; //further validation required politician
		}
		return parent::validate();
	}

	//handle faked range
	protected function checkRanges() {
		$start = $this->data['time_start'] == '--' ? null: $this->data['time_start'];
		$end = $this->data['time_end'] == '--' ? null: $this->data['time_end'];

		$this->manager = new AppointmentManager($this->politician);
		$this->error_party = $this->manager->addAppointment($this->localparty->party, $this->data['category'], $this->localparty->region, $start, $end);
		if($this->error_party != null) $this->errors['party_overlap'] = true;
	}

	/*protected function save(Record $r) {
		$r->politician = $this->data['politician'];
		$r->party = $this->localparty->party;
		$r->region = $this->localparty->region;
		parent::save($r);
	}*/

	public function show($smarty) {
		$smarty->assign('politicians', Politician::getDropDownPoliticiansAllWithoutFunction());
		//$smarty->assign('politician', $this->politician);
		parent::show($smarty);
	}
}

?>