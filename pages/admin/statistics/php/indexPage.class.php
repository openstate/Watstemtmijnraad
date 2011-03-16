<?php

class IndexPage {
	protected $start = '-infinity';
	protected $end = 'infinity';
	
	public function __construct() {
		if (!array_key_exists('role', $_SESSION) || !($_SESSION['role'] instanceof BOUserRoleClerk))
			Dispatcher::forbidden();
		$this->hostname = $_SESSION['role']->getRecord()->subdomain;
		
		$this->start = strftime('%Y-%m-%d', time() - 7*86400);
		$this->end = strftime('%Y-%m-%d', time());
	}
	
	public function processGet($get) {
		if (array_key_exists('clear', $get))
			Dispatcher::header('/statistics/');
		foreach (array('start', 'end') as $key)
			if (@$get[$key] && strtotime($get[$key]) !== false)
				$this->$key = strftime('%Y-%m-%d', strtotime($get[$key]));
	}
	
	public function show($smarty) {
		$smarty->assign('start', $this->start);
		$smarty->assign('end', $this->end);
	
		$smarty->assign('pages', DBs::inst(DBs::SYSTEM)->query('
			SELECT sum(pageviews) AS sum, page_path
			FROM stats_page
			WHERE hostname = % AND start BETWEEN % AND %
			GROUP BY page_path ORDER BY sum DESC LIMIT 10',
			$this->hostname, $this->start, $this->end)->fetchAllCells('page_path'));

		$smarty->assign('locations', DBs::inst(DBs::SYSTEM)->query('
			SELECT sum(visits) AS sum, city||\', \'||region||\', \'||country AS location
			FROM stats_region
			WHERE hostname = % AND start BETWEEN % AND %
			GROUP BY location ORDER BY sum DESC LIMIT 10',
			$this->hostname, $this->start, $this->end)->fetchAllCells('location'));

		$smarty->display('index.html');
	}
}