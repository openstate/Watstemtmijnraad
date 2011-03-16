<?php

require_once('DBs.class.php');
require_once('Region.class.php');


/** Returns list of all gemeentes for autocompletion */
class AllgemeentesPage {
	//[FIXME: cache. browser will cache this anyway, but we can cache on our side too]
	public function processGet($get) {
		header('Content-type: application/json');
		
		try {
			$gems = Region::getDropDownRegionsAllByLevel('Gemeente', 'ORDER BY t.name ASC');
			
			$res = array();
			foreach ($gems as $id => $name) {
				$res[] = array(
					'name' => $name,
					'id' => $id,
					'url' => '/regions/region/'.$id
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