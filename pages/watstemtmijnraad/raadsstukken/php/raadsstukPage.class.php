<?php

require_once('IndexHandler.class.php');
require_once('Region.class.php');
require_once('Raadsstuk.class.php');
require_once('RaadsstukTag.class.php');
require_once('RaadsstukType.class.php');
require_once('RaadsstukCategory.class.php');
require_once('Submitter.class.php');
require_once('Party.class.php');
require_once('PartyVoteCache.class.php');
require_once('VoteCache.class.php');
require_once('Council.class.php');
require_once('Vote.class.php');
require_once('VoteMessage.class.php');

/** Shows raadsstuk information. */
class RaadsstukPage extends IndexHandler {
	private $rs;
	private $council;
	private $members;
	private $preview;
	private $totals;
	private $votes;
	private $party = false;
	private $partyMessages = array();

	public function __construct() {
		$this->sortDefault = 'party_name';
		$this->sortKeys = array('id', 'party_name', 'vote_0', 'vote_1', 'vote_2', 'vote_3');
	}

	public function processGet($get) {
		try {
			//$_SESSION['preview'] -- set by admin/raadsstukken/* (FormPage), preview from admin panel
			$this->preview = @$get['preview'];
			if ($this->preview == 'rs')
				$this->rs = $_SESSION['preview']['rs'];
			else {
				$this->rs = new Raadsstuk(@$get['id']);
				$region = new Region($this->rs->region);
				$host = explode('.', $_SERVER['HTTP_HOST']);
				if ($host[0] != $region->subdomain) {
					$host[0] = $region->subdomain;
					$id = @$get['id'];
					unset($get['id']);
					Dispatcher::header('http://'.implode('.', $host).$_SERVER['SCRIPT_URL'].($get ? '?'.http_build_query($get) : ''));
					if ($id) $get['id'] = $id;
				}
			}

			if(isset($get['party'])){
				$this->party = new Party($get['party']);
			}

			$ids = Dispatcher::sessionUser() ? Dispatcher::sessionUser()->listSiteIds(): User::listDefaultSiteIds();
			if(!isset($ids[$this->rs->site_id])) Dispatcher::forbidden(); //no rights to view raadsstukken of this site

			//override voting info in preview from admin panel
			if ($this->preview == 'vote') $this->rs->result = $_SESSION['preview']['result'];

			if ($this->preview == 'rs') { //list all members that may that vote at specific date
				$council = Council::getCouncilByDate($this->rs->region, strtotime($this->rs->vote_date));
			} else { //
				$council = Council::getCouncilFromRaadsstuk($this->rs);
			}

			//[FIXME: 07-08-2008: if a vote is not found in list, then it will be @supressed, this is weird]
			if ($this->preview)
				$this->council = $council->getView()->getMembersByPartyWithVotesAndNames(@$_SESSION['preview']['votes']);
			else {
				$vote = new Vote();
				$this->council = $council->getView()->getMembersByPartyWithVotesAndNames($vote->loadByRaadsstuk($this->rs->id));
			}

			//(politician_id => party) -- this is the reason why a single bastard can't
			//work for different parties at the same time in the same region
			$this->members = $council->getView()->getPartiesByMember();

			if ($this->preview) {
				$this->totals = new stdClass();
				$this->totals->vote_0 = $this->totals->vote_1 = $this->totals->vote_2 = $this->totals->vote_3 = 0;
				$this->data = array();
				foreach($this->council as $party => $array) {
					$el = new stdClass();
					$el->party = $array['id'];
					$el->party_name = $party;
					$el->vote_0 = $el->vote_1 = $el->vote_2 = $el->vote_3 = 0;
					foreach($array['politicians'] as $key => $politician) {
						if ($politician['vote']) {
							$vt = 'vote_'.$politician['vote']->vote;
							$el->$vt++;
							$this->totals->$vt++;
						}
					}

					//reffer to Vote::getVoteTitleStatic() for explanation
					$el->result = 0; //accept
					if ($el->vote_0 && $el->vote_1) $el->result = -1; //mixed (verdeeld)
					else for ($i = 0; $i < 4; $i++) {
						if ($el->{'vote_'.$i}) { $el->result = $i; break; } //all 'yes', all 'no', at least one 'remember', all 'afwezig'
					}
					$el->result_title = Vote::getVoteTitleStatic($el->result);
					$this->data[] = $el;
				}
			} else {
				$v = new PartyVoteCache();
				$this->data = $v->loadVotesList($this->rs->id, $this->getOrder());

                                $db = DBs::inst(DBs::SYSTEM);
                                foreach($this->data as $raadsstuk) {
                                    $this->votes[$raadsstuk->party] = $db->query("SELECT pol.id, pol.first_name, pol.last_name, v.vote, v.message
                                          FROM rs_votes v
                                          JOIN pol_politicians pol ON v.politician = pol.id
                                          WHERE v.party = '". $raadsstuk->party . "' AND v.raadsstuk = '".$this->rs->id."'")->fetchAllRows();
                                }
                                
               
                $vt = new VoteMessage();
                $vts = $vt->getList('', 'WHERE raadsstuk = ' . $this->rs->id);
                foreach ($vts as $k => $v) {
                	$this->partyMessages[$v->party] = $v->message;
                }
			}

			//[FIX: 07-08-2008: filter out politicians that have not made that fucking vote and still are selected.
			// this may happen because we select all politicians that are allowed to vote and link them with real votes
			// made. So non-voting politicians will have 'vote' = null ]
			foreach ($this->council as $party_name => $members) {
				foreach ($members['politicians'] as $politician_id => $pol) {
					if($pol['vote'] == null) unset($this->council[$party_name]['politicians'][$politician_id]);
				}
				//if party has no votes
				if(sizeof($this->council[$party_name]['politicians']) == 0) unset($this->council[$party_name]);
			}

			//[FIX: 07-08-2008: filter vote cache of non voting parties
			// we need this since we iterate over party-votes in template.
			// Yes, it is posible that a party vote cache record still exists with counters (0 0 0 0) (or non-zero if we have database fucked up)
			// ]
			foreach ($this->data as $k => $row) {
				if(!isset($this->council[$row->party_name])) unset($this->data[$k]);
			}

		} catch (Exception $e) {
			//var_dump($e->__toString());
			//Dispatcher::header('/');
			//Dispatcher::badRequest();
			Dispatcher::notFound();
		}
	}

	public function show($smarty) {
		$t = new RaadsstukTag();
		$c = new RaadsstukCategory();
		$s = new Submitter();
		$extra_par = '';

		$crumbs[0]['url'] = '/regions/region/'.$this->rs->region;
		$crumbs[0]['title'] = $this->rs->region_name;
		if(Dispatcher::inst()->iframe > 0) $extra_par .= '/region/'.$this->rs->region;
		$crumbs['raadsstuk']['title'] = 'Raadsstuk: '.$this->rs->title;

		$header = $this->rs->region_name;
        if($this->rs->ext_url_info)
            $smarty->assign('ext_url_info', $this->rs->ext_url_info);
		$smarty->assign('header', $header);
		$smarty->assign('crumbs', $crumbs);
		$smarty->assign('data', $this->data);
		$smarty->assign('preview', $this->preview);
		$smarty->assign('img', @$_GET['img']); //[FIXME: will fail even in live with missing fonts ]

		$smarty->assign('council', $this->council);
		$smarty->assign('partyMessages', $this->partyMessages);
		$smarty->assign('categories', $this->preview == 'rs' ? $_SESSION['preview']['cat'] : $c->getCategoriesByRaadsstuk($this->rs->id));
		$smarty->assign('tags', $this->preview == 'rs' ? $_SESSION['preview']['tag'] : $t->getTagsByRaadsstuk($this->rs->id));
		$smarty->assign('politician_base_url', POLITICIAN_BASE_URL);

		//build submitters list (this is silly)
		$subs = array();
		$submitters = array();
		foreach ($this->preview == 'rs' ? $_SESSION['preview']['subs'] : $s->getSubmittersNameByRaadsstuk($this->rs->id) as $id => $name) {
			$submitterParty = @$this->members[$id];
			$mem = array();
			$mem[$id] = $name;
			if($submitterParty) {
            	$submitters[$submitterParty->id] = array('id' => $id, 'name' => $name);
				$subs[$submitterParty->id] = array('name' => $submitterParty->name, 'members' => $mem);
			}
		}
        
        $smarty->assign('submitter_party', $submitters);

        if(count($subs) == 1) {
			$party_id = reset(array_keys($subs));
			//show if no iframe or gemeente
			if(Dispatcher::inst()->iframe < 2) $smarty->assign('show_party_link', true);
			if(Dispatcher::inst()->iframe > 1) $extra_par .= '/party/'.$party_id;
			$smarty->assign('party_id', $party_id); //Very understandable piece of code.

			$sub = reset($subs);

			if(count($sub['members']) == 1) {
				$pol_id = reset(array_keys($sub['members']));
				//show if no iframe, gemeente or party
				$smarty->assign('show_politician_link', true);
				$smarty->assign('politician_id', $pol_id); //Very understandable piece of code.
			}
		}

		if($this->party){ //overwrite previous set party params, if necessary
			//show if no iframe or gemeente
			if(Dispatcher::inst()->iframe <= 2) $smarty->assign('show_party_link', true);
			if(Dispatcher::inst()->iframe > 1) $extra_par .= '/party/'.$this->party->id;
			$smarty->assign('party_id', $this->party->id); //Very understandable piece of code.
		}

		$verdict_class = 'other';
		if($this->rs->result == 1) {
			$verdict_class = 'pro';
		} elseif($this->rs->result == 2) {
			$verdict_class = 'contra';
		}

		//show if no iframe
		if(Dispatcher::inst()->iframe < 1) $smarty->assign('show_region_link', true);
		$smarty->assign('submitters', $subs);
		$smarty->assign('verdict_class', $verdict_class);
		$smarty->assign('raadsstuk', $this->rs);
		$smarty->assign('region', new Region($this->rs->region));
		$smarty->assign('totals', $this->preview ? $this->totals : $this->rs);

		if ($this->rs->parent) {
			//on delete set null, so we will never get here record not found
			$smarty->assign('parent', new Raadsstuk($this->rs->parent));
		}

		if (!$this->preview == 'rs') {
			$smarty->assign('amendementen', $this->rs->listChildren(RaadsstukType::AMENDEMENT));
			$smarty->assign('moties', $this->rs->listChildren(RaadsstukType::MOTIE));
		}

        $smarty->assign('votes', $this->votes);
        $smarty->assign('extra_search_params', $extra_par);

		parent::show($smarty);
	}
}

?>
