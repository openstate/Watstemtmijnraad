<?php

require_once('DBs.class.php');
require_once('Region.class.php');
require_once('BOUserRoleClerk.class.php');

class RegionPage {
	public function processGet($get) {
		$regs = Dispatcher::inst()->user->listAllowedRegions();
		foreach ($regs as $reg) {
			if($reg->id == $get['id']) {
				$_SESSION['role'] = new BOUserRoleClerk($get['id']);

				//Needed for the header:
				$tmp_reg = new Region($get['id']);
				if($tmp_reg->level >= Region::getLevel('Provincie')){
					$title = $tmp_reg->level_name.' '.$tmp_reg->name;
				} else {
					$title = $tmp_reg->name;
				}
				$_SESSION['regionTitle'] = $title;
                $_SESSION['regionHidden'] = $tmp_reg->hidden;
                $_SESSION['regionID'] = $tmp_reg->id;

				//var_dump($tmp_reg->used_wizard); die;
				Dispatcher::inst()->header($tmp_reg->used_wizard ? '/raadsstukken/' : '/wizard/');
				die();
			}
		}
		Dispatcher::forbidden();
	}
}

?>
