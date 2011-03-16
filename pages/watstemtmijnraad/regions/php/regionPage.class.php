<?php

require_once('IndexHandler.class.php');
require_once('Region.class.php');
require_once('Raadsstuk.class.php');
require_once('Category.class.php');
require_once('Tag.class.php');
require_once('Level.class.php');
require_once('PartyVoteCache.class.php');
require_once('Page.class.php');
require_once('Appointment.class.php');


define('REGION_RAADSSTUKKEN_PER_PAGE', 5);
define('REGION_TAGS_PER_PAGE', 15);


/** Shows region information. */
class RegionPage extends IndexHandler {
	private $region;
	private $parties;
	private $raadsstukken;
	private $categories;
	private $tags;
	private $category;
	private $tag;
    private $votes_pro = array();
    private $votes_contra = array();
    private $votes_total = array();
    private $party_members = array();


	public function show($smarty) {
		$class = false;
		$bar = false;

		$bar_width = array();
		foreach($this->raadsstukken as $voting) {
			$contra = $voting->vote_1;
			$total = $voting->vote_0 + ($voting->vote_1); //Don't count 'not voted'

			if(!$total == 0){
				$bar_width[$voting->id] = (($contra / $total) * 100)-4; //magical number -4 has to do with padding
            }else{ //This should never happen. If it does anyway, make it 50-50
				$bar_width[$voting->id] = 46; //50-4, magical number -4 has to do with padding
            }
		}

		$crumbs = array();
		if($this->region->parent) {
			$crumbs[] = array(
				'url' => '/regions/region/'.$this->region->parent,
				'title' => $this->region->parent_name,
			);
		}
		$crumbs['region']['title'] = $this->region->name;

		$header = $this->region->name;

		if($this->region->countChildren() > 0) {
			//[FIXME: non localized strings hardcoded!]
			switch ($this->region->level) {
				case 1: $title = 'Landen'; break;
				case 2: $title = 'Provincies'; break;
				case 3: $title = 'Gemeentes'; break;
				case 4: $title = 'Stadsdelen'; break;
				default: $title = "THIS CAN'T HAPPEN. GO FIX YOUR DB AND CODE!";
			}

			$crumbs[] = array(
				'url' => '/regions/overview/'.$this->region->id,
				//[FIXME: Non localized string hardcoded ]
				'title' => $title,
			);
		}

		//static pages lnks
		$p = new Page();
		$pages = $p->getVisiblePages($this->region->id);

        $smarty->assign('party_members', $this->party_members);
		$smarty->assign('pages', $pages);
        $smarty->assign('votes_pro', $this->votes_pro);
        $smarty->assign('votes_contra', $this->votes_contra);
		$smarty->assign('tags', $this->tags);
		$smarty->assign('categories', $this->categories);
		$smarty->assign('max_rs_count', REGION_RAADSSTUKKEN_PER_PAGE);
		$smarty->assign('bar_width', $bar_width);
		$smarty->assign('message', $this->region->formatName());
		$smarty->assign('raadsstukken', $this->raadsstukken);
		$smarty->assign('region', $this->region);
		$smarty->assign('parties', $this->parties);
		$smarty->assign('crumbs', $crumbs);
		$smarty->assign('header', $header);

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

		$ex_par = '';
		if(Dispatcher::inst()->iframe > 0) $ex_par .= '/region/'.$this->region->id;
		$smarty->assign('extra_url_params', $ex_par);

		parent::show($smarty);
	}

	public function processGet($get) {
		if(!count($get)) Dispatcher::header('/');

		try {
			$this->region = new Region(@$get['id']);
		} catch (Exception $e) {
			Dispatcher::notFound();
		}

		if($this->region->level < Region::$LEVELS['Gemeente']){
			Dispatcher::header('/regions/overview/'.$this->region->id);
		}
		
		$host = explode('.', $_SERVER['HTTP_HOST']);
		if ($host[0] != $this->region->subdomain) {
			$host[0] = $this->region->subdomain;
            $url = 'http://'.implode('.', $host);
            if(Dispatcher::inst()->preview) $url .= "/?preview=1";
			Dispatcher::header($url);
		} elseif ($_SERVER['SCRIPT_URL'] != '/') {
			Dispatcher::header('/'.($get ? '?'.http_build_query($get) : ''));
		}

		//extra filter parameters
        $this->category  = (isset($get['category']) && ($category = trim($get['category'])) != '')? intval($category): null;
        $this->tag = (isset($get['tag']) && ($tag = trim($get['tag'])) != '')? intval($tag): null;

        //fetch raadsstukken
        $r = new Raadsstuk();
        $v = new PartyVoteCache();
        $db = DBs::inst(DBs::SYSTEM);
        $votes = array();
        $tmpVotePro = 0;
        $tmpVoteContra = 0;
        $this->raadsstukken = $r->listRecentByRegion(REGION_RAADSSTUKKEN_PER_PAGE, $this->region->id, $this->category,
        	$this->tag, Dispatcher::inst()->iframe == 0);
        $app = new Appointment();

        $radpks = array();
        foreach($this->raadsstukken as $raadsstuk) {
            $radpks[] = $raadsstuk->id;
        }

        $specials = $v->loadSpecialList($radpks);
        foreach($specials as $spec) {
            if($spec->special == 1) {
                $this->votes_pro[$spec->raadsstuk] = $spec;
            } else {
                $this->votes_contra[$spec->raadsstuk] = $spec;
            }
        }

        /*
        foreach($this->raadsstukken as $raadsstuk) {
            $votes = $v->loadVotesList($raadsstuk->id);
            foreach($votes as $vote) {
                if($vote->vote_0 > $tmpVotePro) {
                    $tmpVotePro = $vote->vote_0;
                    $this->votes_pro[$raadsstuk->id] = $vote;
                }
                if($vote->vote_1 > $tmpVoteContra) {
                    $tmpVoteContra = $vote->vote_1;
                    $this->votes_contra[$raadsstuk->id] = $vote;
                }
            }
            $tmpVotePro = 0;
            $tmpVoteContra = 0;
        }*/

        //fetch popular tags and categories
		$this->tags = Tag::listPopularByRegion($this->region->id, null, REGION_TAGS_PER_PAGE);
		$this->categories = Category::listPlainByRegion($this->region->id);

		//fetch parties
		$this->parties = Party::listActiveParties($this->region->id);

        foreach($this->parties as $party) {
            $this->party_members[$party->id] = $app->countByParty($party->id, $get['id']);
        }
	}
}

?>
