<?php

class Style extends Record {
	protected $data = array(
		'id' => null,
		'name' => null,
		'color1' => null,
		'color2' => null,
		'color3' => null,
		'color4' => null,
		'color5' => null,
		'color6' => null,
		'color7' => null,
		'color8' => null,
		'slogan' => null,
		'font_family' => null,
		'font_color' => null,
		'font_size' => null,
		'bg_color' => null,
		'logo' => null,
		'fields' => null,
	);

	protected $extraCols = array(
		'name' => 'r.name',
	);

	protected $tableName = 'sys_styles';
	protected $multiTables = 'sys_styles t JOIN sys_regions r ON t.id = r.id';

	public function loadByTitle($title) {
		$result = $this->getList('', 'WHERE '.$this->db->formatQuery("name ILIKE %", $title));
		if (count($result)) return reset($result);
		else return self::getDefault();
	}

	public function assign($smarty) {
		foreach ($this->data as $key => $value)
			$smarty->assign($key, $value);
	}

	public function setId($id) {
		$this->data['id'] = $id;
	}

	public static function getDefault() {
		$s = new self();
		$s->color1 = '04b2d9';
		$s->color2 = '666558';
		$s->color3 = '999966';
		$s->color4 = 'ffffff';
		$s->color5 = '';
		$s->color6 = '';
		$s->color7 = '';
		$s->color8 = '';
		$s->slogan = '';
		$s->font_family = '';
		$s->font_color = '';
		$s->font_size = '';
		$s->bg_color = '';
		$s->logo = null;

		return $s;
	}
}