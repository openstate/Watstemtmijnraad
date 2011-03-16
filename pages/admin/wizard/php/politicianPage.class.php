<?php

require_once('LocalParty.class.php');
require_once('Appointment.class.php');

class PoliticianPage {
	public function __construct() {
		if (!array_key_exists('role', $_SESSION) || !($_SESSION['role'] instanceof BOUserRoleClerk))
			Dispatcher::forbidden();

		$this->region = $_SESSION['role']->getRecord()->id;
	}

	public function processPost($post) {
		if (array_key_exists('prev', $post)) {
			Dispatcher::header('/wizard/party');
		}

		if (array_key_exists('delete', $post)) {
			$app = new Appointment();
			@list($pol, $par) = explode('-', $post['delete']);
			$apps = $app->getList('',DBs::inst(DBs::SYSTEM)->formatQuery(
				'WHERE % >= time_start AND %1 < time_end AND region = %2 AND politician = %3 AND party = %4',
				$_SESSION['setup_wizard']['date'], $this->region, $pol, $par));
			foreach ($apps as $a) {
				$a->time_end = $_SESSION['setup_wizard']['date'];
				$a->save();
			}
			Dispatcher::header('/wizard/politician/?open='.$par);
		}
		if (array_key_exists('next', $post)) {
			Dispatcher::header('/wizard/style/');
		}
	}

	public function processGet($get) {
		$this->open = @$get['open'];
	}

	public function show($smarty) {
		$lp = new LocalParty();
		$lps = $lp->getList('', DBs::inst(DBs::SYSTEM)->formatQuery(
			'WHERE % >= time_start AND %1 < time_end AND region = %2',
			$_SESSION['setup_wizard']['date'], $this->region),
			'ORDER BY p.name');
		$app = new Appointment();
		$apps = $app->getList('', DBs::inst(DBs::SYSTEM)->formatQuery(
			'WHERE % >= time_start AND %1 < time_end AND region = %2',
			$_SESSION['setup_wizard']['date'], $this->region),
			'ORDER BY pol.name_sortkey');
		if ($apps) {
			$pol = new Politician();
			$pols = $pol->getList('', 'WHERE id IN ('.implode(', ', array_map(create_function('$a', 'return $a->politician;'), $apps)).')');
 		} else
			$pols = array();
		$smarty->assign('parties', $lps);
		$smarty->assign('appointments', $apps);
		$smarty->assign('politicians', $pols);
		$smarty->assign('open', $this->open);
		$smarty->display('politician.html');
	}
}