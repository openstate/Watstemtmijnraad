<?php

require_once('IndexHandler.class.php');
require_once('LocalParty.class.php');
require_once('Party.class.php');
require_once('Politician.class.php');
require_once('Raadsstuk.class.php');
require_once('Region.class.php');
require_once('Vote.class.php');
require_once('Tag.class.php');
require_once('PartyVoteCache.class.php');
require_once('Page.class.php');

define('PARTY_RAADSSTUKKEN_PER_PAGE', 5);
define('PARTY_TAGS_PER_PAGE', 15);


/** Shows region information. */
class PartyPage extends IndexHandler {
	private $party;
	private $region = null;
    private $tags;
    private $categories;
    private $tag;
    private $category;
    private $raadsstukken = array();
    private $member_count;


	public function show($smarty) {
		if(!$this->region) { //show select region page
			$regs = Region::listForParty($this->party->id);

			if($this->party->short_form) {
				$header = $this->party->short_form;
			} else {
				$header = $this->party->name;
			}

			$smarty->assign('party', $this->party);
			$smarty->assign('regions', $regs);
			$smarty->assign('header', $header);
			$smarty->assign('crumbs', true);

			$smarty->display('partyPage_region_select.html');
			return;
		}

		//header
		if($this->party->short_form) {
			$headerName = $this->party->short_form;
		} else {
			$headerName = $this->party->name;
		}

		$header = '<span class="subtitle">'.$this->region->name.'</span>';
		$header .= $headerName;

		//bread crumbs menu
        if($this->region->parent != '') {
            $parent_region = Region::loadById($this->region->parent);
            $crumbs[0]['url'] = '/regions/region/'.$this->region->parent;
            $crumbs[0]['title'] = $parent_region['name'];
            $crumbs[1]['url'] = '/regions/region/'.$this->region->id;
            $crumbs[1]['title'] = $this->region->name;
        } else {
            $crumbs[0]['url'] = '/regions/region/'.$this->region->id;
            $crumbs[0]['title'] = $this->region->name;
        }

		$crumbs['party']['title'] = $this->party->name;


		//Other stuff
		$votings = $this->raadsstukken;
		$bar_width = array();
		$party_pro = array();
		$party_contra = array();
		$party_total = array();
		foreach($votings as $voting) {
			$tmp_vc = new PartyVoteCache();
			$tmp_vc = $tmp_vc->loadVotesListWithParty($voting, $this->party);
			$party_voting = array_shift($tmp_vc);

			//Fix the bar!
			$contra = $voting->vote_1;
			$total = $voting->vote_0 + ($voting->vote_1); //Don't count 'not voted'
			if(!$total == 0) $bar_width[$voting->id] = intval((($contra / $total) * 100) - 4); //magical number -4 has to do with padding
			else $bar_width[$voting->id] = 46;

			$party_pro[$voting->id] = $party_voting->vote_0;
			$party_contra[$voting->id] = $party_voting->vote_1;
			$party_total[$voting->id] = $party_voting->vote_0 + $party_voting->vote_1 + $party_voting->vote_2 + $party_voting->vote_3;
		}

		$tmpLocalParty = LocalParty::loadLocalParty($this->party->id, $this->region->id);
		$p = new Politician();
		$tmpMems = array_map('reset', $p->loadByParty($tmpLocalParty->id, false, 'ORDER BY name_sortkey'));

		if($tmpMems) $partyMembers = $p->getList('', 'WHERE id IN ('.implode(', ', $tmpMems).')');
		else $partyMembers = array();


		//fetch correlations
		$db = DBs::inst(DBs::SYSTEM);
		//ugly query because it is really hard to fetch parties, which are active
		//in the same region as current party. DB design sucks.
		$fit = $db->query('SELECT p.id, p.name, p.image, floor(pc.total_fit * 100) as fit
		                   FROM pol_party_correlations pc
		                   JOIN pol_parties p ON pc.party_2 = p.id
		                   WHERE pc.active
		                   AND pc.party_1 = %i
		                   AND p.id IN (
		                     	SELECT pref.party
		                     	FROM pol_party_regions pr
		                     	JOIN pol_party_regions pref ON pref.time_start BETWEEN pr.time_start AND pr.time_end
		                     	                            OR pref.time_end   BETWEEN pr.time_start AND pr.time_end

		                     	WHERE pr.party = %i AND pr.region = %i
		                   		AND pref.region = pr.region)',
				$this->party->id, $this->party->id, $this->region->id)->fetchAllRows();

		foreach($fit as $key => $cor_party){
			$ids = array();
			foreach($votings as $v){
				$ids[] = $v->id;
			}
            if(!empty($ids)) {
                $count = $this->party->getCommonRaadsstukCount($cor_party['id'], $ids);
            }

			if($count == 0){
				$cor_party['fit'] = false;
				$fit[$key] = $cor_party;
			}
		}

		//static pages lnks
		$p = new Page();
		$pages = $p->getVisiblePages($this->region->id);
        $smarty->assign('member_count', $this->member_count);
		$smarty->assign('pages', $pages);
		$smarty->assign('header', $header);
		$smarty->assign('crumbs', $crumbs);
        $smarty->assign('tags', $this->tags);
        $smarty->assign('categories', $this->categories);
		$smarty->assign('corr_info', $fit);
		$smarty->assign('max_rs_count', PARTY_RAADSSTUKKEN_PER_PAGE);
		$smarty->assign('bar_width', $bar_width);
		$smarty->assign('party', $this->party);
		$smarty->assign('region', $this->region);
		$smarty->assign('party_members', $partyMembers);
		$smarty->assign('votings', $votings);

		$smarty->assign('party_pro', $party_pro);
		$smarty->assign('party_contra', $party_contra);
		$smarty->assign('total_votes', $party_total);

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

		$ex_par = '';
		if($iframe > 0) $ex_par .= '/region/'.$this->region->id;
		if($iframe > 1) $ex_par .= '/party/'.$this->party->id;
		$smarty->assign('extra_url_params', $ex_par);

		parent::show($smarty);
	}

	public function processGet($get) {
		if(!count($get)) Dispatcher::header('/');

		try {
			$this->party = new Party(@$get['id']);
		} catch (Exception $e) {
			Dispatcher::notFound();
		}

		//present user with the choice or region
		if(!isset($get['region'])) return;

		try{
			$this->region = new Region(@$get['region']);
		} catch(NoSuchRecordException $e) { //No region
			Dispatcher::notFound();
		}

		//extra filter parameters
        $this->category  = (isset($get['category']) && ($category = trim($get['category'])) != '')? intval($category): null;
        $this->tag = (isset($get['tag']) && ($tag = trim($get['tag'])) != '')? intval($tag): null;

        //fetch raadsstukken
        $r = new Raadsstuk();
        $this->raadsstukken = $r->listRecentByParty(PARTY_RAADSSTUKKEN_PER_PAGE, $this->party->id, $this->region->id, $this->category, $this->tag);

		//fetch popular tags and categories
		$this->tags = Tag::listPopularByRegion($this->region->id, $this->party->id, PARTY_TAGS_PER_PAGE);
		$this->categories = Category::listPlainByRegion($this->region->id, $this->party->id);

        $app = new Appointment();
        $this->member_count = $app->countByParty($this->party->id, $this->region->id);

	}
}

?>
