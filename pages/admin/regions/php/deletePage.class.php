<?php

require_once('Region.class.php');

class deletePage {
	private $region;
	
	public function processGet($get) {
		//Deletiong region is really dangerous operation, that cascades
		//a lot of things. So double check everything.
		try {
			$this->region = new Region();
			$this->region->load(intval($get['id']));
		} catch (Exception $e) {
			Dispatcher::notFound();
		}
		
		if(isset($get['confirm'])) {
			try {
				$this->region->delete();
				Dispatcher::header('/regions');
			} catch (Exception $e) {
				$_SESSION['error'] = 'Kan geselecteerde regio niet verwijderen. Er is een ander region of partij daarvan afhankelijk is!';
				Dispatcher::header('/regions/?region='.$get['id']);
			}
		}
	}
	
	public function show($smarty) {
		$smarty->assign('region', $this->region);
		$smarty->display('deletePage.html');
	}
}

?>