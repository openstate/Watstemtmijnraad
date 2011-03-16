<?php

require_once('Appointment.class.php');
require_once('IndexHandler.class.php');
require_once('LocalParty.class.php');
require_once('Party.class.php');
require_once('Politician.class.php');
require_once('Raadsstuk.class.php');
require_once('Region.class.php');
require_once('Vote.class.php');
require_once('Tag.class.php');


define('POLITICIAN_RAADSSTUKKEN_PER_PAGE', 5);
define('POLITICIAN_TAGS_PER_PAGE', 15);


/** Shows region information. */
class PoliticianPage extends IndexHandler {
	private $politician;
	private $region;
	private $appointment;
	private $party;
	private $limit = 5;
    private $tags;
    private $categories;
    private $tag;		//current tag
    private $category;  //current category
    private $raadsstukken = array();

	public function show($smarty) {
		if($this->appointment->time_start != null && $this->appointment->time_start != '-infinity') {
			$member_since = $this->appointment->time_start;
		}
		$rs = new Raadsstuk();

		//Get politician's party and other info
		$app = new Appointment();
		if(!$this->region) {
			$app = $app->loadByPolitician($this->politician->id);//, $limit='LIMIT 1');
        } else {
			$app = $app->loadByPoliticianAndRegion($this->politician->id, $this->region->id);
        }

		$app = array_shift($app);
		$party = new Party($app->party);

		if(!$this->region)
			$this->region = new Region($app->region);

		if($app->time_start != null && $app->time_start != '-infinity') {
			$member_since = $app->time_start;
		} else {
			$member_since = false;
		}

		//Header
		if($this->party->short_form) {
			$headerParty = $this->party->short_form;
		} else {
			$headerParty = $this->party->name;
		}

		$header = '<span class="subtitle">'.$headerParty.' - '.$this->region->name.'</span>';
		$header .= $this->politician->formatName();
		$smarty->assign('header', $header);

        // crumbs
        if($this->region->parent != '') {
            $parent_region = Region::loadById($this->region->parent);
            $crumbs[0]['url'] = '/regions/region/'.$this->region->parent;
            $crumbs[0]['title'] = $parent_region['name'];
            $crumbs[1]['url'] = '/regions/region/'.$this->region->id;
            $crumbs[1]['title'] = $this->region->name;
            $crumbs[2]['url'] = '/parties/party/'. $this->party->id . '?region='.$this->region->id;
            $crumbs[2]['title'] = $this->party->name;
        } else {
            $crumbs[0]['url'] = '/regions/region/'.$this->region->id;
            $crumbs[0]['title'] = $this->region->name;
            $crumbs[1]['url'] = '/parties/party/'. $this->party->id . '?region='.$this->region->id;
            $crumbs[1]['title'] = $this->party->name;
        }

        $crumbs['politician']['title'] = $this->politician->first_name . ' ' . $this->politician->last_name;
		$smarty->assign('crumbs', $crumbs);


		//Other stuff
		$tmpLocalParty = LocalParty::loadLocalParty($this->party->id, $this->region->id);
		if($tmpLocalParty){
			$tmpMems = array_map('reset', $this->politician->loadByParty($tmpLocalParty->id, false, $order='ORDER BY name_sortkey'));
		} else {
			$tmpMems = false;
		}

		if($tmpMems) {
			$p = new Politician();
			$partyMembers = $p->getList('', 'WHERE id IN ('.implode(', ', $tmpMems).')');
		} else $partyMembers = array();


		$stats['votes'] = Vote::countVotes($this->politician, $this->region);
		$stats['real_votes'] = Vote::countRealVotes($this->politician, $this->region);
		$stats['submits'] = $this->politician->getCountSubmit();

		$smarty->assign('votings', $this->raadsstukken);
        $smarty->assign('tags', $this->tags);
        $smarty->assign('categories', $this->categories);
		$smarty->assign('max_rs_count', POLITICIAN_RAADSSTUKKEN_PER_PAGE);
		$smarty->assign('politician', $this->politician);
		$smarty->assign('stats', $stats);
		$smarty->assign('party', $this->party);
		$smarty->assign('region', $this->region);
		$smarty->assign('member_since', $member_since);
		$smarty->assign('party_members', $partyMembers);

		//I want objects
		if($this->category){
			$filter_cat = new Category($this->category);
			$smarty->assign('filter_cat', $filter_cat);
		}

		if($this->tag) {
			$filter_tag = new Tag($this->tag);
			$smarty->assign('filter_tag', $filter_tag);
		}

		$smarty->assign('cur_category', $this->category);
		$smarty->assign('cur_tag', $this->tag);

		$iframe = Dispatcher::inst()->iframe;
		if($iframe < 2) $smarty->assign('show_region_link', true);
		if($iframe < 3) $smarty->assign('show_party_link', true);

		$ex_par = '';
		if($iframe > 0) $ex_par .= '/region/'.$this->region->id;
		if($iframe > 1) $ex_par .= '/party/'.$this->party->id;
		$smarty->assign('extra_url_params', $ex_par);

		parent::show($smarty);
	}


	public function processGet($get) {
		if(!count($get)) Dispatcher::header('/');

		try {
			$this->politician = new Politician(@$get['id']);
		} catch (Exception $e) {
			Dispatcher::notFound();
		}

		/* If region is specified, then show the appointment together with raadsstukken in that region.
		   All raadsstukken will be reachable. If region is not specified, then fetch the most recent
		   appointment.

		   Note: while technically a politician can be active for many parties in different regions, it doesn't
		   happen in real life.
		*/
		$app = new Appointment();
		if(isset($get['region'])) { //recent appointment in given region.
			try {
				$this->region = new Region(@$get['region']);
			} catch (Exception $e) {
				Dispatcher::notFound();
			}

        	$app = $app->loadByPoliticianAndRegion($this->politician->id, $this->region->id, 'ORDER BY time_end DESC', 'LIMIT 1');
        	$this->appointment = reset($app);
		} else { //no region given, last active appointment
			//Note: we select a random last (probably active) appointment now! in most cases this will be one record, but
			//it is technically possible that politician works for many parties in different regions.
			$app = $app->loadByPolitician($this->politician->id, 'ORDER BY time_end DESC', 'LIMIT 1');
			$this->appointment = reset($app);
			$this->region = new Region($this->appointment->region);
		}
		if(!$this->appointment) Dispatcher::notFound();
        $this->party = new Party($this->appointment->party);

        //extra filter parameters
        $this->category  = (isset($get['category']) && ($category = trim($get['category'])) != '')? intval($category): null;
        $this->tag = (isset($get['tag']) && ($tag = trim($get['tag'])) != '')? intval($tag): null;
		$this->politician = new Politician(@$get['id']);
                $party = new Appointment();
                $party = $party->loadByPolitician(@$get['id']);


		if(isset($get['region'])) {
			$this->region = new Region(@$get['region']);
			$party = array_shift($party);

			$db = DBs::inst(DBs::SYSTEM);
			$tags = $db->query('(SELECT t.name, t.id, count(*) FROM rs_raadsstukken_tags rs JOIN sys_tags t ON t.id = rs.tag GROUP BY t.name, t.id) UNION (SELECT t.name, t.id, 0 AS count FROM sys_tags t LEFT JOIN rs_raadsstukken_tags rs ON t.id = rs.tag WHERE rs.tag IS NULL) ORDER BY count DESC LIMIT 15')->fetchAllRows();
			foreach($tags as $tag) {
				$this->tags[] = $tag;
			}

			$categories = $db->query('SELECT * FROM sys_categories')->fetchAllRows();
			foreach($categories as $category) {
				$this->categories[] = $category;
			}
		}

        //fetch raadsstukken
        $r = new Raadsstuk();
        $this->raadsstukken = $r->listRecentByPoliticianAndRegion(POLITICIAN_RAADSSTUKKEN_PER_PAGE, $this->politician->id, $this->region->id, $this->category, $this->tag);
        
		//fetch popular tags and categories
		$this->tags = Tag::listPopular(POLITICIAN_TAGS_PER_PAGE);
		$this->categories = Category::listByRegion($this->region->id);
	}
}

?>
