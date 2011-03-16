<?php

require_once('indexPageBase.class.php');
require_once('Pager.class.php');
require_once('Party.class.php');
require_once('LocalParty.class.php');

class indexPage extends indexPageBase {
	protected $sortDefault = 'name';
	protected $itemsPerPage = 20;
	protected $pager = null;
	protected $superAdmin;
	
	public function __construct() {
	
		$this->sortKeys = array(
			'id' => null,
			'name' => null,
		);
	}
	

	public function processGet($get) {
		$party = new Party();

		$this->superAdmin = Dispatcher::inst()->user->isSuperAdmin();
		
		if (!isset($get['region']) || $get['region'] == -1) $region = null;
		elseif ($get['region'] == 'false') $region = false;
		else $region = intval($get['region']);
		
		$this->pager = new Pager(Party::countParties($region), intval(@$get['start']), $this->itemsPerPage);
		$this->loadData(Party::listParties($region, $this->getOrder(), $this->pager->getCurrent(), $this->pager->getLimit()));
	}

	public function show($smarty) {
		$smarty->assign('superAdmin', $this->superAdmin);
		if (!$this->superAdmin) {
			$lp = new LocalParty();
			$smarty->assign('localParties', $lp->loadByRegion($_SESSION['role']->getRecord()->id));
			$smarty->assign('now', date('Y-m-d H:i:s'));
			$smarty->assign('regionName', $_SESSION['role']->getRecord()->formatName());
		}
		$smarty->assign('pager', $this->pager->getHTML('', 'start', 'sortcol='.$this->sorting['col'].'&amp;sort='.$this->sorting['dir'].(isset($_GET['region']) ? '&amp;region='.(integer)$_GET['region'] : '')));
		parent::show($smarty);
	}
}




?>