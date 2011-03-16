<?php

require_once('SearchPage.class.php');
require_once('Region.class.php');
require_once('Appointment.class.php');

/** Shows region information. */
class SearchRegionPage extends SearchPage {
	private $regions;
	private $type;
    private $votes_last_month_sorted;
    private $councillors_by_region;

	public function show($smarty) {
		parent::show($smarty);
        $crumbs[0]['url'] = null;
		$crumbs[0]['title'] = 'Zoekresultaten';
        $crumbs[1]['title'] = 'Kies een regio';

        $smarty->assign('councillor_counts', $this->councillors_by_region);
        $smarty->assign('vote_counts', $this->votes_last_month_sorted);
		$smarty->assign('crumbs', $crumbs);
		$smarty->assign('regions', $this->regions);
		$smarty->assign('type', $this->type);

		$smarty->display('searchRegionPage.html');
	}

	public function processGet($get) {
		if(!count($get)){
			Dispatcher::header('/');
		}

		$type = @$get['type'];
		$query = @$get['region'];
		$this->search($query, $type);
	}

	private function search($name, $type) {
		$db = DBs::inst(DBs::SYSTEM);
		$parts = preg_split('#\\W+#', $name);

		$search = array();
		foreach ($parts as $pt) {
			if(($pt = trim($pt)) == '') continue;
			$pt = $db->formatQuery('%s', $pt);
			$search[] = trim($pt, "'\"");
		}
		//on empty search, return to home page
		if(!$search) Dispatcher::header('/');

		$search = "%".implode('%', $search)."%";
		$type = intval($type); //since we do escaping manually already, don't mix %i

        // count fot votes last month
		$r = new Region();
		$this->regions = $r->getList('', "WHERE t.level = {$type} AND t.name ILIKE '{$search}'", 'ORDER BY t.level, t.name');

		if(count($this->regions) == 1) {
            $region = reset($this->regions);
			Dispatcher::header('/regions/region/'.$region->id);
		} else {
            $region_ids = array();
            foreach($this->regions as $region) {
                $region_ids[] = $region->id;
            }

            $votes_last_month = Vote::countVotesLastMonth($region_ids);
            $total_results = count($votes_last_month);
            if(!$votes_last_month && $total_results == 0){
            	$votes_last_month = array(0);
            }else {
                foreach($votes_last_month as $vote) {
                    $this->votes_last_month_sorted[$vote['region']] = $vote['count'];
                }
            }
        }

        //counts for councillors per region
        if(count($region_ids) > 0){
	        $app = new Appointment();
	        $councillors = $app->countByRegion($region_ids);
	        foreach($councillors as $councillor) {
	            $this->councillors_by_region[$councillor['region']] = $councillor['count'];
	        }
        }

	}
}

?>
