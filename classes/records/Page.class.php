<?php

class Page extends Record {
	protected $data = array(
		'url'     => null,
		'region'  => null,
		'title'   => null,
		'content' => null,
		'linkText'=> null,
		'showInMenu' => null,
	);

	protected $tableName = 'pg_pages';
	protected $defaultNames = array("'home'", "'search'", "'about'");

	/**
	 * Fetch page by url.
	 *
	 * @param string $url URL to match
	 * @param Region|integer $region associated region object, null for region free page
	 * @return Page found page or null if page doesn't exists
	 */
	public function loadByUrl($url, $region = null) {
		$result = $this->getList($this->db->formatQuery('WHERE url = %s', $url).' AND region '.(null == $region ? 'IS NULL' : $this->db->formatQuery('= %i', _id($region))));
		if (count($result)) return reset($result);
		else {
			if ($region != null) return $this->loadByUrl($url);
			else return null;
		}
	}

	/**
	 * Returns true if page record exists (search by url).
	 *
	 * @param string $url URL to match
	 * @param Region|integer $region associated region object, null for region free page
	 * @return boolean true if record does exists
	 */
	public function exists($url, $region = null) {
		return $this->db->query('select 1 from pg_pages where url = %s and region '.(null == $region ? 'IS NULL' : '= %i'), $url, _id($region))->fetchCell() == 1;
	}

	/**
	 * Returns true if URL of this page is one of special urls.
	 *
	 * The following urls are 'special':
	 *
	 *   - home   -- home page
	 *   - search -- search page
	 *   - about  -- about page
	 *
	 * @return boolean true if URL is one of listed items above, false otherwise
	 */
	public function isSpecial() {
		return $this->region == null && in_array("'".$this->url."'", $this->defaultNames);
	}


	/**
	 * List all pages visible in given region.
	 * The page is visible if it belongs to given $region and has showInMenu set to true.
	 *
	 * @param Region|integer $region associated region object, null for region free page
	 * @param boolean $show if set, then only pages with showInMenu = true will be selected
	 * @return Page visible pages
	 */
	public function getVisiblePages($region, $show = true) {
		return $this->getList('WHERE region '.(null == $region ? 'IS NULL' : $this->db->formatQuery('= %i', _id($region))).($show? ' AND "showInMenu" = 1': ''));
	}

	/**
	 * List all pages of given $region or null-region.
	 *
	 * @param Region|integer $region associated region object
	 * @return array of Page list of pages
	 */
	public function getDefaultPages($region) {
		$pages = $this->getList($this->db->formatQuery('WHERE t.url IN ('.implode(',', $this->defaultNames).') AND (region = %i OR region IS NULL) ORDER BY region', _id($region)));

		$result = array();
		foreach ($pages as $p) $result[$p->url] = $p;

		return $result;
	}
}