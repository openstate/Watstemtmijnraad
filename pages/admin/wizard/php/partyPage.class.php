<?php

require_once('LocalParty.class.php');
require_once('Region.class.php');
require_once('Appointment.class.php');

class PartyPage {
	const TOP_LEVEL = 2;

	public function __construct() {
		if (!array_key_exists('role', $_SESSION) || !($_SESSION['role'] instanceof BOUserRoleClerk))
			Dispatcher::forbidden();

		$this->region = $_SESSION['role']->getRecord()->id;
		$lp = new LocalParty();
		$lps = $lp->getList('', DBs::inst(DBs::SYSTEM)->formatQuery(
			'WHERE % >= time_start AND %1 < time_end AND region = %2',
			$_SESSION['setup_wizard']['date'], $this->region));
		$this->current = array();
		foreach ($lps as $lp)
			$this->current[$lp->party] = $lp;
	}

	public function processPost($post) {
		if (array_key_exists('party', $post)) {
			foreach ($this->current as $lp)
				if (!in_array($lp->party, $post['party'])) {
					$lp->time_end = $_SESSION['setup_wizard']['date'];
					$lp->save();
					unset($this->current[$lp->party]);
					$app = new Appointment();
					$apps = $app->getList('', DBs::inst(DBs::SYSTEM)->formatQuery(
						'WHERE party = % AND region = % AND % >= time_start AND %3 < time_end',
						$lp->party, $this->region->id, $_SESSION['setup_wizard']['date']));
					foreach ($apps as $app) {
						$app->time_end = $_SESSION['setup_wizard']['date'];
						$app->save();
					}
				}
			foreach ($post['party'] as $p)
				if (!array_key_exists($p, $this->current)) {
					$lp = new LocalParty();
					$lp->party = $p;
					$lp->region = $this->region;
					$lp->time_start = $_SESSION['setup_wizard']['date'];
					$lp->time_end = 'infinity';
					$lp->save();
					$this->current[$lp->party] = $lp;
				}
			Dispatcher::header('/wizard/politician');
		}
	}
	
	public function show($smarty) {
		$r = new Region();
		$r->load($this->region);
		$p = new Party();
		$ps = $p->getList('', DBs::inst(DBs::SYSTEM)->formatQuery(
			'WHERE t.id IN (SELECT party FROM pol_party_regions WHERE % >= time_start AND %1 < time_end AND region IN (%2, %3, %4))',
			$_SESSION['setup_wizard']['date'], $this->region, $r->parent, self::TOP_LEVEL), 'ORDER BY t.name');
		$smarty->assign('parties', $ps);
		$smarty->assign('current', array_keys($this->current));
		$smarty->display('party.html');
	}
}