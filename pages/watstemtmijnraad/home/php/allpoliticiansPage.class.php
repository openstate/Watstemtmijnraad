<?php

require_once('DBs.class.php');
require_once('Politician.class.php');


/** Returns list of all politicians for autocompletion */
class AllpoliticiansPage {
	
	//[FIXME: this should probably not be cached...]
	public function processGet($get) {
		header('Content-type: application/json');
		
		try {
			$gems = Politician::getDropDownPoliticiansAll();
			
			$res = array();
			foreach ($gems as $id => $name) {
				$res[] = array(
					'name' => $name,
					'id' => $id,
					'url' => '/politicians/politician/'.$id
				);
			}
			
			//output result
			echo json_encode($res);
		} catch (Exception $e) {
			echo '/* error! */';
		}

		die();
	}
}