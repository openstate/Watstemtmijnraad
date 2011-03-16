<?php

require_once('Politician.class.php');

class PoliticianPage {
	protected $party = null;
	protected $region = null;
	protected $expired = null;

	public function processGet($get) {
		$this->party = (int) @$get['party'];
		$this->region = (int) @$get['region'];
		$this->expired = isset($get['expired']);
	}

	public function show($smarty) {
		//[FIXME: non-localized string!]
        if(_id($this->party) == 0) {
            $politicians = array('' => 'Alle') + Politician::getDropDownPoliticiansWithoutParty($this->region, $this->expired);
        } else {
            $politicians = array('' => 'Alle') + Politician::getDropDownPoliticians($this->party, $this->region, $this->expired);
        }
		echo(implode("\n", array_map(create_function('$a,$b', 'return $a."||".trim($b);'), array_keys($politicians), $politicians)));
		die;
	}
}

?>