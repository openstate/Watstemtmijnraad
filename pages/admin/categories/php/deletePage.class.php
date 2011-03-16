<?php

require_once('Category.class.php');

class deletePage {
	private $id = '';	
	
	public function processGet($get) {
		if(!isset($get['id']) || !ctype_digit($get['id']))
			Dispatcher::header('../');
		
		$this->id = $get['id'];
		
		$category = new Category();
				
		try {
			$category->load($this->id); //not found exception
			$category->delete(); //restricted exception
		} catch (Exception $e) {
			if($this->id) {
				require_once('Appointment.class.php');
				$apps = Appointment::listForCategory($this->id);
				//we can't store Appintment in session because of 'Incomplete class' problem
				foreach ($apps as $p) {
					$_SESSION['apps_del'][] = array(
						'id' => $p->id,
						'politician' => $p->politician,
						'party' => $p->party_name,
						'region' => $p->region_name
					);
				}
			}
			
			$_SESSION['error'] = 'De gekozen categorie kan niet worden verwijderd.';
		}
	}
	
	public function show($smarty) {		
		Dispatcher::header('../');
	}
}

?>