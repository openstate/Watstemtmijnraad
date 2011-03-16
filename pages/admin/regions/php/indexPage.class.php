<?php

require_once('indexPageBase.class.php');
require_once('Pager.class.php');
require_once('Region.class.php');

class indexPage extends indexPageBase {
	protected $sortDefault = 'level';
	protected $itemsPerPage = 20;
	protected $pager = null;
	
	protected $sortKeys = array(
			'id' => false,
			'id' => null,
			'level_name' => null,
			'parent_name' => null,
			'name' => null,
			'level' => null,
			'parent' => null,
		);

	public function processGet($get) {		
		$region = new Region();
		
		$this->loadData($region->getList($this->getWhere(),
			$this->getOrder()
		));		
	}

	public function show($smarty) {		
		if (isset($_SESSION['error'])) {
			$smarty->assign('error', $_SESSION['error']);
			unset($_SESSION['error']);
		}

		//$this->loadFromObject();
		parent::show($smarty);
	}	
}

?>