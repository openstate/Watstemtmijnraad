<?php

class RaadsstukCategory extends Record {

	protected $data = array(
		'raadsstuk' => null,
		'category' => null,
		'category_name' => null,
	);

	protected $extraCols = array(
		'category_name' => 'sc.name',
	);

	protected $tableName = 'rs_raadsstukken_categories';
	protected $multiTables = 'rs_raadsstukken_categories t join sys_categories sc on t.category = sc.id';

	public function getCategoriesByRaadsstuk($raadsstuk) {
		return $this->buildList('WHERE raadsstuk = '.intval(_id($raadsstuk)), 'buildCategoriesByRaadsstuk');
	}

	public function getCategoriesByRaadsstukOnName($raadsstuk) {
		return $this->buildList('WHERE raadsstuk = '.intval(_id($raadsstuk)), 'buildCategoriesByRaadsstukOnName');
	}

	private function buildList($where, $callback) {
		$result = array();
		foreach($this->getList($where) as $t) {
			$this->$callback($t, $result);
		}
		return $result;
	}

	public function buildCategoriesByRaadsstuk($record, &$list) {
		$list[$record->category] = $record->category_name;
	}

	private function buildCategoriesByRaadsstukOnName($record, &$list) {
		$list[$record->category_name] = $record;
	}

    public function countByCategory($category) {
        return $this->db->query('SELECT COUNT(*) FROM '.$this->tableName.' WHERE category = %i', $category)->fetchCell();
    }
	public function deleteByRaadsstuk($raadsstuk) {
		$this->db->query('DELETE FROM '.$this->tableName.' WHERE raadsstuk = %i', $raadsstuk);
	}
}

?>