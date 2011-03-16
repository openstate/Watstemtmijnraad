<?php

require_once('Appointment.class.php');
require_once('Party.class.php');
require_once('Politician.class.php');
require_once('Region.class.php');
require_once('SearchPage.class.php');

/** Shows region information. */
class SearchPoliticianPage extends SearchPage {
	private $politicians;

	public function show($smarty) {
		parent::show($smarty);

		$polparties = array();
		$polregions = array();

		if(count($this->politicians)) {
			$app = new Appointment();
			//[FIXME: assumption getList() always returns id => obj, array_keys($this->politicians) is a list of politician id's]
			$app = $app->loadActiveByPolitician(array_keys($this->politicians));
			$regions = array();
			$parties = array();
			
			foreach ($app as $ap) {
				$regions[$ap->politician] = $ap->region;
				$parties[$ap->politician] = $ap->party;
			}
			
			$party = new Party();
			$dbparties = $parties? $party->getList('', 'WHERE t.id IN ('.implode(', ', $parties).')'): array();
			$polparties = array();
			foreach ($parties as $pol => $party) $polparties[$pol] = $dbparties[$party];
			unset($party); unset($dbparties); unset($parties);
			
			$region = new Region();
			$dbregions = $regions? $region->getList('', 'WHERE t.id IN ('.implode(', ', $regions).')'): array();
			$polregions = array();
			foreach ($regions as $pol => $region) $polregions[$pol] = $dbregions[$region];
			unset($region); unset($dbregions); unset($regions);
		}


		$crumbs[0]['url'] = '/search/submit/Zoek';
		$crumbs[0]['title'] = 'Zoekresultaten';
        $crumbs[1]['title'] = 'Kies een politicus';
        
		$smarty->assign('crumbs', $crumbs);
		$smarty->assign('politicians', $this->politicians);
		$smarty->assign('parties', $polparties);
		$smarty->assign('regions', $polregions);
		$smarty->display('searchPoliticianPage.html');
	}

	public function processGet($get) {
		if(!count($get)){
			Dispatcher::header('/');
		}

		$query = @$get['politician'];
		$this->search($query);
	}

	//TODO: Use JOIN to make it easier for the databaseserver (which is btw the longest English word that can be typed with one hand!)
	private function search($query, $include_reg = false) {
		$db = DBs::inst(DBs::SYSTEM);
		$words = array_filter(array_map('trim', preg_split('#\\W+#', $query)));
		
		$search = array();
		foreach ($words as $pt) {
			$pt = $db->formatQuery('%s', $pt);
			$search[] = trim($pt, "'\"");
		}
		//on empty search redirecto to home page
		if(!$search) Dispatcher::header('/');
		
		$search = "%".implode('%', $search)."%";
		
		//enable region search
		if($search > 1 || $include_reg) {
			$reg_query = " OR t.id IN (SELECT politician FROM pol_politician_functions pf JOIN sys_regions r ON pf.region = r.id WHERE now() < pf.time_end AND r.name ILIKE '{$search}')";
		} else $reg_query = '';
		
		$p = new Politician();
		$this->politicians = $p->getList('', "WHERE (t.first_name ILIKE '{$search}' OR t.last_name ILIKE '{$search}' OR t.name_sortkey ILIKE '{$search}') {$reg_query}", 'ORDER BY t.name_sortkey');
		
		if(count($this->politicians) == 0 && !$include_reg) {
			$this->search($query, true);
		} elseif(count($this->politicians) == 1) {
            $pol = reset($this->politicians);
			Dispatcher::header('/politicians/politician/'.$pol->id);
		}
	}
}

?>
