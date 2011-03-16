<?php

require_once('HNSSyncedRecord.class.php');

class RaadsstukTag extends HNSSyncedRecord {

	protected $data = array(
		'raadsstuk' => null,
		'tag' => null,
		'tag_name' => null,
	);

	protected $extraCols = array(
		'tag_name' => 'st.name',
	);

	protected $tableName = 'rs_raadsstukken_tags';
	protected $multiTables = 'rs_raadsstukken_tags t join sys_tags st on t.tag = st.id';

	public function getTagsByRaadsstuk($raadsstuk) {
		return $this->buildList('WHERE raadsstuk = '.$raadsstuk, 'buildTagsByRaadsstuk');
	}

	public function getTagsByRaadsstukOnName($raadsstuk) {
		return $this->buildList('WHERE raadsstuk = '.$raadsstuk, 'buildTagsByRaadsstukOnName');
	}

	private function buildList($where, $callback) {
		$result = array();		
		foreach($this->getList($where) as $t) {
			$this->$callback($t, $result);
		}
		return $result;		
	}

	private function buildTagsByRaadsstuk($record, &$list) {
		$list[$record->tag] = $record->tag_name;
	}

	private function buildTagsByRaadsstukOnName($record, &$list) {
		$list[$record->tag_name] = $record;
	}

	public function deleteByRaadsstuk($raadsstuk) {
		$this->db->query('DELETE FROM '.$this->tableName.' WHERE raadsstuk = %', $raadsstuk);
	}


    ///////////////////////////////////////////////////////
	//                                                   //
	//     HNS.dev SYNCHRONISATION RELATED FUNCTIONS     //
	//                                                   //
	///////////////////////////////////////////////////////

	protected function shouldSyncToHns() { return false; }

	public function save() {
		$do_sync = empty($this->id);

		parent::save();

		try {
			if (!$do_sync || HNS_DISABLE_SYNC) return;

			$raadsstuk = new Raadsstuk($this->raadsstuk);

			if (!$raadsstuk->show) return;

			$query = $this->addOrRemoveQuery('+');

			// __DUMMY__ not used, but needed to prevent call to getHnsMapping()
			$this->insertHnsEntry($query, '__DUMMY__');
		} catch(Exception $ex){
			if(DEVELOPER){
				throw $ex;
			} else {
				//Mail exception to exceptions@getlogic.nl, but do not show to the user.
				$this->mailExceptionToDeveloper($ex);
			}
		}

		return $value;
	}

	
	protected function extractHnsId($result, $createdType) {
		// HNS does not return the IDs of the tags it creates. So we don't check for one.
		return true;
	}

	public function __get($name){
		switch ($name){
			case 'hns_document_id':
				$raadsstuk = new Raadsstuk($this->raadsstuk);

				if (!$raadsstuk->show) throw new HnsApiError('Tag should not be saved! Raadsstuk->show == false');

				if(!$raadsstuk->hasHnsId()) {
					$raadsstuk->save();
				}

				return $raadsstuk->hnsId();
				break;
			case 'hns_tag_name':
				if (strlen($this->tag_name == 0)) {
					$tag = new Tag($this->tag);

					return $tag->name;
				}

				return $this->tag_name;
				break;
			default:
				return parent::__get($name);
		}
	}

	public function delete() {
		$value = parent::delete();

		$query = $this->addOrRemoveQuery('-');

		$this->execute($query);

		return $value;
	}

	protected function addOrRemoveQuery($operator = null) {
		$operator = ($operator == '-') ? '-' : '+';

		$tagName = $this->sanitizeValue($this->hns_tag_name);

		$query = "
			<update>
				<document id=\"{$this->hns_document_id}\">
					<tags>{$operator}{$tagName}</tags>
				</document>
			</update>
		";

		return $query;
	}
}

?>
