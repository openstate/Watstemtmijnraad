<?php

require_once('Party.class.php');
require_once('Raadsstuk.class.php');


define('MAX_RAADSSTUKKEN_BY_PARTY', 10);

/**
* Displays raadsstukken of a specific party.
* 
* The raadsstuk is of specific party if:
*   - there is a vote of a politicus
*   - that politicus has an appointment in selected region covering vote data of the raadsstuk
*   - that appointment if of the party
* 
* Note: latest patches have introduces party link in rs_votes, we can use it instead of
* all the things manually.
* 
* @author Sardar Yumatov (ja.doma@gmail.com)
*/
class PartyPage {
	private $raadsstukken;
	private $party;
    private $theme;

	public function processGet($get) {
		if(!isset($get['id'])) Dispatcher::notFound();
		
		$limit = isset($get['limit'])? min(MAX_RAADSSTUKKEN_BY_PARTY, intval($get['limit'])): MAX_RAADSSTUKKEN_BY_PARTY;
		if($limit < 1) $limit = 1;

        if($get['theme']) {
            if($get['theme'] == 'dark') {
                $this->theme = 'dark';
            } else if ($get['theme'] == 'light') {
                $this->theme = 'light';
            } else {
                $this->theme = 'dark';
            }
        }

		//lookup for party
		$this->party = new Party();
		try {
			$this->party->load(intval($get['id']));
		} catch (Exception $e) {
			Dispatcher::notFound();
		}

		//in specific region?
		$region = isset($get['region'])? intval($get['region']): null;
		
		//lookup for the raadsstukken
		$r = new Raadsstuk();
		$this->raadsstukken = $r->listRecentByParty($limit, intval($get['id']), $region);
	}
	
	public function show($smarty) {
		$smarty->assign('raadsstukken', $this->raadsstukken);
		$smarty->assign('num', sizeof($this->raadsstukken));
		$smarty->assign('party', $this->party);
		
		$smarty->assign('theme', $this->theme); //or 'light'
		
		$smarty->setBaseTemplate('../templates/small_iframe.html');
		$smarty->display('partyPage.html');
	}
}

?>