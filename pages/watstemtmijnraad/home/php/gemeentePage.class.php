<?php

require_once('DBs.class.php');
require_once('Region.class.php');

class GemeentePage {
	public function processGet($get) {
		try {
			$r = new Region(@$get['id']);
			$regions = $r->selectChildren(true, true);

			$ret = '';
			foreach ($regions as $id => $row) {
				$rd = $row['region'];
				if($row['count'] > 0) $ret .= $rd->id.'||'.$rd->name.($rd->parent != $r->id? ' ('.$regions[$rd->parent]['region']->name.')': '')."\n";
			}

			//[FIXME: non localized string]
			if($ret == '') $ret = '-1||Geen'; //default
			echo rtrim($ret);
		} catch (Exception $e) {
			echo '';
		}

		die();
	}
}