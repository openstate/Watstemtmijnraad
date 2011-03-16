<?php

class RaadsstukType extends Record {
	const RAADSVOORSTEL = 1;
	const INITIATIEFVOORSTEL = 2;
	const AMENDEMENT = 3;
	const MOTIE = 4;
	const BURGERINITIATIEF = 5;

	protected $data = array(
		'name'        => null,
	);

	protected $tableName = 'rs_raadsstukken_type';

	/**
	 * Returns Smarty ready list of all raadsstuk types.
	 * @return array (id => name)
	 */
	public static function getAssociativeOnId() {
		$r = new self();

		$result = array();
		foreach($r->getList('', '', 'ORDER BY t.id ASC') as $t) $result[$t->id] = $t->name;

		return $result;
	}

	/**
	 * Returns Smarty ready list of all raadsstuk types including 'everything' option.
	 * @return array (id => name)
	 */
	public static function getSearchTypes() {
		//[FIXME: non localized string in code!]
		return array('' => 'Alle') + self::getAssociativeOnId();
	}

	/** Serialize to XML */
	public function toXml($xml) {
		return $xml->getTag('type').
			$xml->fieldToXml('id', $this->id, false).
			$xml->fieldToXml('name', $this->name, true).
			$xml->getTag('type', true);
	}
}

?>
