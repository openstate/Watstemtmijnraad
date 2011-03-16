<?php

require_once('HNSSyncedRecord.class.php');

class Submitter extends HNSSyncedRecord {
	protected $data = array(
		'raadsstuk' => null,
		'politician' => null,
		'politician_name' => null,
	);

	protected $extraCols = array(
		'politician_name' => "p.first_name || ' ' || p.last_name",
	);

	protected $tableName = 'rs_raadsstukken_submitters';
	protected $multiTables = 'rs_raadsstukken_submitters t JOIN pol_politicians p ON t.politician = p.id';

	public function getSubmittersByRaadsstuk($raadsstuk) {
		$list = array();
		foreach ($this->getList('', 'WHERE raadsstuk = '.intval(_id($raadsstuk))) as $s) {
			$list[] = $s->politician;
		}
		return $list;
	}

	public function getSubmittersNameByRaadsstuk($raadsstuk) {
		$list = array();
		foreach ($this->getList('', 'WHERE raadsstuk = '.intval(_id($raadsstuk))) as $s) {
			$list[$s->politician] = $s->politician_name;
		}
		return $list;
	}

	public function deleteByRaadsstuk($raadsstuk) {
		$this->db->query("
			DELETE FROM
				sys_hns_ids
			WHERE
				record_type = 'Submitter'
				AND record_id IN (
					SELECT id FROM {$this->tableName} WHERE raadsstuk = %i
				)
			", $raadsstuk);

		$this->db->query('DELETE FROM '.$this->tableName.' WHERE raadsstuk = %i', $raadsstuk);
	}

    ///////////////////////////////////////////////////////
	//                                                   //
	//     HNS.dev SYNCHRONISATION RELATED FUNCTIONS     //
	//                                                   //
	///////////////////////////////////////////////////////

    public function __get($name){
    	switch($name) {
			case 'hns_author_id':
				$politician = new Politician();
				$politician->load($this->politician);

				if (!$politician->hasHnsId()) $politician->save();

				return $politician->hnsId();
			case 'hns_document_id':
				$raadsstuk = new Raadsstuk();
				$raadsstuk->load($this->raadsstuk);

				if (!$raadsstuk->hasHnsId()) $raadsstuk->save();

				return $raadsstuk->hnsId();
    		default:
    			return parent::__get($name);
    	}
    }

	public function verifyCanSyncToHns() {
		$raadsstuk = new Raadsstuk($this->raadsstuk);

		if (!$raadsstuk->show) {
			throw new HnsCannotSyncError("Cannot sync Submitter({$this->id}): Raadsstuk.show == false");
		}

		return parent::verifyCanSyncToHns();
	}

	protected $alreadyExistsInHns = false;

	protected function fetchHnsid() {
		$hnsId = parent::fetchHnsId();

		if ($hnsId) $this->alreadyExistsInHns = true;

		return $hnsId;
	}

	// HNS.authors == many-to-many. No need to update
	protected function updateHnsEntry() { return true; }

	protected function getHnsMapping() {
		$mapping = array(
			'hnsTable' => 'author',
			'fields'   => array(
				'hns_author_id'   => 'person',
				'hns_document_id' => 'document'
			),
		);

		return $mapping;
	}

	protected $uniques = array(
		'hns_author_id'   => 'person.id',
		'hns_document_id' => 'document.id'
	);
}

?>
