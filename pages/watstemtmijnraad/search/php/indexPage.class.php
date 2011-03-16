<?php

require_once('SearchPage.class.php');
require_once('SearchEngine.class.php');
require_once('SearchPager.class.php');
require_once('SearchQuery.class.php');
require_once('Politician.class.php');
require_once('Party.class.php');
require_once('Region.class.php');
require_once('Appointment.class.php');

class IndexPage extends SearchPage {
	private $searchData = false;
	private $searchFilter;
	private $searchTotals;
	private $searchFields;
	private $searchHeader;
	private $limit = 10;
	private $start;
	private $fts;
	private $focus = false; //false, 'par', 'pol' or 'reg'

	//private $skipList = array('start');

	public function processGet($get) {
		if (count($_POST)) return;
		parent::processGet($get);
		if (isset($get['submit']) || strlen(@$get['q']) || isset($get['region'])) {
			$this->start = isset($get['start']) ? (int)$get['start'] : 0;
			$this->searchFields = $this->assign($get);
			$this->search($this->searchFields);
		} else {
			Dispatcher::header('/');
		}
	}

	public function processPost($post) {
		$s = '';
		foreach ($post as $key => $value) {
			//Exceptions for external searches
			if ('' == $value || 'iframe' == $key) continue;
			$s .= '/'.$key.'/'.urlencode($value);
		}

		//[FIXME: URL parameter hardcoded, iframe setting could be handled by mod_rewrite]
		Dispatcher::header('/search'.$s.(Dispatcher::inst()->iframe? '?iframe='.Dispatcher::inst()->iframe: ''));
	}

	protected function assign($get) {
		$data = array();
		if (isset($get['q']) || isset($get['region'])) {
			$data['q'] = @$get['q'];
			/*if (null != $r = Dispatcher::inst()->region) {
				$data['q'] .= ' '.$r->name;
			}*/

			if(isset($get['region'])) {
				$data['region'] = @$get['region'];
			}
			if(isset($get['politician_id'])) {
				$data['politician_id'] = @$get['politician_id'];
			}
			if(isset($get['party'])) {
				$data['party'] = @$get['party'];
			}

			$cat_query = 'SELECT id FROM sys_categories WHERE name ILIKE %s';
			$cat = DBs::inst(DBs::SYSTEM)->query($cat_query, @str_replace('+', ' ', $data['q']))->fetchRow();

			if(isset($cat['id'])) {
				$data['category'] = $cat['id'];
			}

		} else {
			$data['region'] = @$get['region'];
			$data['code'] = @$get['code'];
			$data['title'] = @str_replace('+', ' ', $get['title']);
			$data['summary'] = @str_replace('+', ' ', $get['summary']);
			$data['category'] = @$get['category'];
			$data['type'] = @$get['type'];
			$data['vote_date'] = @$get['Date_Year']? $get['Date_Year'].(@$get['Date_Month']?'-'.$get['Date_Month'].(@$get['Date_Day']?'-'.$get['Date_Day']:''):''): null;
			$data['party'] = @$get['party'];
			$data['submitter_id'] = @$get['submitter_id'];
			$data['politician_id'] = @$get['politician_id'];
			$data['tags'] = @str_replace('+', ' ', $get['tags']);
		}
		return $data;
	}

	protected function search($params) {
		$se = &$_SESSION['search'];

		//if (null == $se || $se['id'] != serialize($this->searchFields)) {
			$engine = new SearchEngine($params);
			$se = array('id' => serialize($this->searchFields), 'time' => time(), 'results' => $engine->getResults(), 'fts' => $engine->isFts(), 'filter' => $engine->getFilterInformation(), 'vco' => $engine->getVoteCacheOption());

			switch ($se['vco']) {
				case SearchEngine::VOTE_CACHE_POLITICIAN:
					$total = array(0, 0, 0, 0);
					foreach($se['results'] as $result) {
						$total[0] += $result->vote_0;
						$total[1] += $result->vote_1;
						$total[2] += $result->vote_2;
						$total[3] += $result->vote_3;
					}
					$se['totals'] = $total;
					break;
				case SearchEngine::VOTE_CACHE_PARTY:
					$total = array(0, 0, 0);
					foreach($se['results'] as $result) {
						$total[0] +=  $result->vote_0 && !$result->vote_1 ? 1 : 0;
						$total[1] += !$result->vote_0 &&  $result->vote_1 ? 1 : 0;
						$total[2] +=  $result->vote_0 &&  $result->vote_1 ? 1 : 0;
					}
					$se['totals'] = $total;
					break;

				default: //vote cache all
					$se['totals'] = array();
					break;
			}
		//}

		if (isset($this->searchFields['submitter_id']) && $this->searchFields['submitter_id'] != null) { //Isn't this doing the same thing twice?
			//can not throw NoSuchRecordException because SearchEngine already checked this (not failed)
			$politician = new Politician((int)$_GET['submitter_id']);
			$app = $politician->getLatestAppointment();
			//[FIXME: non localized string]
			$this->searchHeader = array('Moties / Amendementen ingediend door: ', $politician->formatName(), $app->getParty()->name, $app->getRegion()->formatName());
		}

		if(isset($params['politician_id'])) { //Politician
			$this->focus = 'pol';
		} elseif(isset($params['party'])) { //Party
			$this->focus = 'par';
		} elseif(isset($params['region'])) { //Region
			$this->focus = 'reg';
		}

		$this->fts = $se['fts'];
		$this->searchData = $se['results'];
		$this->searchFilter = $se['filter'];
		$this->searchTotals = $se['totals'];

		$this->pager = new SearchPager(count($this->searchData), $this->start, $this->limit);
	}

	public function show($smarty) {
		parent::show($smarty);
		$cnt = count($this->searchData);
		if ($this->start >= $cnt) $this->start = (($cnt - $this->limit) >= 0) ? $cnt - $this->limit : 0;

		$q = SearchQuery::fromString($_SERVER['SCRIPT_URL']);

		$formdata = array_slice($this->searchData, $this->start, $this->limit);
		$crumbs = true;

		$searchAll = false;
		if($q->q == '-all' && $this->fts){
			$searchAll = true;
		}

		$bar_width = array();
		foreach($formdata as $voting) {
			$contra = $voting->vote_1;
			$total = $voting->vote_0 + ($voting->vote_1); //Don't count 'not voted'

			if(!$total == 0)
				$bar_width[$voting->id] = (($contra / $total) * 100)-4; //magical number -4 has to do with padding
			else //This should never happen. If it does anyway, make it 50-50
				$bar_width[$voting->id] = 46; //50-4, magical number -4 has to do with padding

			if($this->focus == 'par') { //Party
				$total_votes[$voting->id] = $voting->vote_0 + $voting->vote_1 + $voting->vote_2 + $voting->vote_3;
				$smarty->assign('total_votes', $total_votes);
			}
		}

		//Sidebars?
		$crumbs = array();
		if($this->focus == 'pol'){
			$politician = new Politician($q->politician_id);
			$smarty->assign('politician', $politician);

			$app = new Appointment();
			$app = $app->loadByPoliticianAndRegion($politician->id, $q->region, 'ORDER BY time_end DESC', 'LIMIT 1');//, $limit='LIMIT 1');
			if(!empty($app)) { //empty appointment? search page is broken, this can't happen
				$app = array_shift($app);
				$party = new Party($app->party);
				$region = new Region($app->region);

				$crumbs[0]['url'] = '/politicians/politician/'.$politician->id.'?region='.$region->id;
				$crumbs[0]['title'] = $politician->formatName(false);

				if($searchAll) {
					$crumbs[1]['title'] = 'Raadsstukken';
				} else {
					$crumbs[1]['title'] = 'Zoeken';
				}

				$smarty->assign('party', $party);
				$smarty->assign('region', $region);

				$tmpLocalParty = LocalParty::loadLocalParty($party->id, $region->id);
				$tmpMems = array_map('reset', $politician->loadByParty($tmpLocalParty->id, false, $order='ORDER BY name_sortkey'));

				$partyMembers = $tmpMems? $politician->getList('', 'WHERE id IN ('.implode(', ', $tmpMems).')'): array();
				$smarty->assign('party_members', $partyMembers);

				$iframe = Dispatcher::inst()->iframe;
				if($iframe < 2) $smarty->assign('show_region_link', true);
				if($iframe < 3) $smarty->assign('show_party_link', true);
				$smarty->assign('show_politician_link', true);

				if($iframe > 0) $this->data['show_region'] = false;
				if($iframe > 1) $this->data['show_party'] = false;
			}
		}
		elseif($this->focus == 'par') {

			$party = new Party($q->party);
			$smarty->assign('party', $party);

			$crumbs[0]['url'] = '/parties/party/'.$party->id;
			$crumbs[0]['title'] = $party->name;

			if($searchAll) {
				$crumbs[1]['title'] = 'Raadsstukken';
			} else {
				$crumbs[1]['title'] = 'Zoeken';
			}

			$iframe = Dispatcher::inst()->iframe;
			if($q->region) {
				$tmpLocalParty = LocalParty::loadLocalParty($party->id, $q->region);
				$p = new Politician();
				$tmpMems = array_map('reset', $p->loadByParty($tmpLocalParty->id, false, 'ORDER BY name_sortkey'));
				if($tmpMems) $partyMembers = $p->getList('', 'WHERE id IN ('.implode(', ', $tmpMems).')');
				else $partyMembers = array();

                $smarty->assign('region', new Region($q->region));
				$smarty->assign('party_members', $partyMembers);
				$crumbs[0]['url'] .= '?region='.$q->region;

				if($iframe < 2) $smarty->assign('show_region_link', true);
				if($iframe > 0) $this->data['show_region'] = false;

			} //if region is not specified, we can not tell anything about party members
			  //worst idea will be to show all party members from all regions

			if($iframe < 3) $smarty->assign('show_party_link', true);
			if($iframe > 1) $this->data['show_party'] = false;
		}
		elseif($this->focus == 'reg') {
			$region = new Region($q->region);
			$smarty->assign('region', $region);

			$crumbs[0]['url'] = '/regions/region/'.$region->id;
			$crumbs[0]['title'] = $region->name;

			if($searchAll) {
				$crumbs[1]['title'] = 'Raadsstukken';
			} else {
				$crumbs[1]['title'] = 'Zoeken';
			}
            
			$smarty->assign('sb_parties', Party::listActiveParties($region->id));

			if($region->level < Region::$LEVELS['Gemeente']){
				$smarty->assign('no_region_bar', true);
			}

			$iframe = Dispatcher::inst()->iframe;
			if($iframe < 2) $smarty->assign('show_region_link', true);
			if($iframe > 0) $this->data['show_region'] = false;
		}

		//hide details
		if(Dispatcher::inst()->iframe > 0) $smarty->assign('form', $this->data);

		$smarty->assign('focus', $this->focus);
		$smarty->assign('searchAll', $searchAll);
		$smarty->assign('crumbs', $crumbs); //TODO: Get real crumbs
		$smarty->assign('formdata', $formdata);
		$smarty->assign('bar_width', $bar_width);
		if($this->searchHeader) $smarty->assign('sheader', $this->searchHeader);
		//$smarty->assign('filter', $this->searchFilter);
		$smarty->assign('totals', $this->searchTotals);
		$smarty->assign('time', @$q->Date_Year.'-'.@$q->Date_Month.'-'.@$q->Date_Day);
		$smarty->assign('fts', $this->fts);
		$smarty->assign('search_params', $this->searchFields);
		$smarty->assign('stats', array('count' => $cnt,
		                               'start' => $this->start,
		                               'end' => (($end = $this->start + $this->limit) <= $cnt) ? $end : $cnt));

		$q->remove('start');
		$smarty->assign('pager', $this->pager->getHTML('/search'.$q->toString(), 'start', ''));
		$smarty->assign('warning', $cnt == SearchEngine::MAX_RESULTS);
		$smarty->display('indexPage.html');
	}
}

?>