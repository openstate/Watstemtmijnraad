<?php
abstract class RegionPageBase {
	protected $data;
	
	protected $dataLoaded = false;
	
	protected $sortDefault = 'id';
	protected $sortDirDefault = 'asc';
	protected $sorting = array('col' => '', 'dir' => 'asc');
	protected $sortKeys;
	
	//protected $includeExpired = false;
	
	public function __construct() {
		$this->sortKeys = array(
			'id' => null,
			'party_name' => null,
		);
	}

	public function loadFromObject($where = '', $order = '', $limit = '') {
		require_once('LocalParty.class.php');
		$loader = new LocalParty();
		
		//(!$this->includeExpired ? 'now() < time_end AND ' : '')
		$parties = $loader->getList('', 'WHERE region = ' . $_SESSION['role']->getRecord()->id, 'ORDER BY party_name', $limit, '(CASE WHEN now() < time_end THEN 0 ELSE 1 END) as expired');
		$party_ids = array();
		$party_index = array();
		foreach ($parties as $pt) {
			$party_ids[] = $pt->id;
			$party_index[$pt->expired? 'expired': 'current'][$pt->party] = $pt;
		}

		//load politician appointments
		require_once('Politician.class.php');
		$loader = new Politician();
		$pols = $loader->loadByParty($party_ids, true, 'ORDER BY name_sortkey, last_name, first_name');
		$politicians_index = array();
		
		foreach ($pols as $pol) {
			$pol['formated_name'] = Politician::staticFormatName($pol);
			$politicians_index[$pol['party']][$pol['expired']? 'expired': 'current'][$pol['id']] = $pol;
		}
		
		$this->party_index = $party_index;
		$this->politicians_index = $politicians_index;
	}
	
	public function show($smarty) {
		$smarty->assign('parties', $this->party_index);
		$smarty->assign('appointments', $this->politicians_index);
		$smarty->assign('template_path', $smarty->getCurrDir().'../content/');
		$smarty->display('regionPage.html');
	}
}

?>