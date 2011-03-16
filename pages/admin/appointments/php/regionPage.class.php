<?php

require_once('regionPageBase.class.php');
require_once('SecurityException.class.php');
require_once('Appointment.class.php');

class RegionPage extends RegionPageBase {
	const HELPER_CLASS_SUFFIX = 'AppointmentRegionHelper';

	protected $sortDefault = 'party_name';
	protected $region;
    protected $politician_list = array();

	public function processGet($get) {
		$role = $_SESSION['role'];
        
        if (!isset($role)) {
			Dispatcher::header('/');
		} else {
			$className = get_class($role) . self::HELPER_CLASS_SUFFIX;
			require_once($className.'.class.php');
			$helper = new $className($role);
			$this->region = $helper->getID($get);
			try { //this thing allows only Clerk furhter (Selection by region)
				  //others will be redirected either to /appointments/region_id (from politician selection)
				  //or to /appointments/party/party_id (from party selection)
				  //in short: this code is over complicated
				$helper->isAllowed($this->region);
			} catch (SecurityException $e) {
				$helper->forbidden();
			}
		}
	}

	public function show($smarty) {
		$this->loadFromObject($this->region);
        //$smarty->assign('politicians', $this->politician_list);
		$smarty->assign('region', $_SESSION['role']->getRecord());
		//$smarty->assign('includeExpired', $this->includeExpired);
		parent::show($smarty);
	}
}

?>