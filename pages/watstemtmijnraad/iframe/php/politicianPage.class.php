<?php

require_once('Politician.class.php');
require_once('Raadsstuk.class.php');


define('MAX_RAADSSTUKKEN_BY_POLITICIAN', 10);


/**
* Displays raadsstukken of a specific politician.
* The raadsstuk is of specific politician if that politician has voted for that raadsstuk.
* 
* @author Sardar Yumatov (ja.doma@gmail.com)
*/
class PoliticianPage {
	private $raadsstukken;
	private $politician;
	private $theme;
    
	public function processGet($get) {
		if(!isset($get['id'])) Dispatcher::notFound();
		$limit = isset($get['limit'])? min(MAX_RAADSSTUKKEN_BY_POLITICIAN, intval($get['limit'])): MAX_RAADSSTUKKEN_BY_POLITICIAN;
		if($limit < 1) $limit = 1;
		
		$this->politician = new Politician();
		try {
			$this->politician->load(intval($get['id']));
		} catch (Exception $e) {
			Dispatcher::notFound();
		}
		if($get['theme']) {
            if($get['theme'] == 'dark') {
                $this->theme = 'dark';
            } else if ($get['theme'] == 'light') {
                $this->theme = 'light';
            } else {
                $this->theme = 'dark';
            }
        }

		//lookup for the raadsstukken
		$r = new Raadsstuk();
		$this->raadsstukken = $r->listRecentByPolitician($limit, intval($get['id']));
	}
	
	
	public function show($smarty) {
		$smarty->assign('raadsstukken', $this->raadsstukken);
		$smarty->assign('num', sizeof($this->raadsstukken));
		$smarty->assign('politician', $this->politician);
		
		$smarty->assign('theme', $this->theme); //'dark' or 'light'
		
		$smarty->setBaseTemplate('../templates/small_iframe.html');
		$smarty->display('politicianPage.html');
	}
}

?>