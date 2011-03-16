<?php

require_once('IndexHandler.class.php');
require_once('Raadsstuk.class.php');
require_once('Category.class.php');
require_once('Pager.class.php');

class IndexPage extends IndexHandler {
	private $itemsPerPage = 20;
    private $archive_page = 0;
	private $pager;

	public function __construct() {
		$this->sortKeys = array('id', 'title', 'site', 'code', 'vote_date', 'category_name', 'type_name',);
		$this->sortDefault = 'vote_date';
		$this->sortDirDefault = 'desc';
		$cat = new Category();
		$this->categories = $cat->getList('', 'WHERE t.id > 0', 'ORDER BY name');
	}

	public function processGet($get) {
		$rs = new Raadsstuk();
		
		$this->archive = array_key_exists('archive', $get);
        if($this->archive) {
            $this->archive_page = $get['archive'];
        }
		$where = $this->archive ? 'AND show > 0' : 'AND show = 0';
		$join = '';
		$db = DBs::inst(DBs::SYSTEM);
		
		if (@$get['code'])
			$where .= $db->formatQuery('AND code ILIKE %', '%'.$get['code'].'%');
		if (@$get['title'])
			$where .= $db->formatQuery('AND t.title ILIKE %', '%'.$get['title'].'%');
		if (@$get['vote_date']) {
			$date = implode('-', array_reverse(explode('-', $get['vote_date'])));
			if (strtotime($date) !== false)
				$where .= $db->formatQuery('AND vote_date = %', $date);
			else
				unset($get['vote_date']);
		}
		if (@$get['category']) {
			if (array_key_exists($get['category'], $this->categories)) {
				$join .= 'JOIN rs_raadsstukken_categories rc ON rc.raadsstuk = t.id';
				$where .= $db->formatQuery(' AND rc.category = %', $get['category']);
			} else
				unset($get['category']);
		}
		if (@$get['summary']) {
			$db->query("SELECT set_curcfg('dutch')");
			$join .= $db->formatQuery('JOIN rs_raadsstukken_vectors rv ON t.id = v.id, to_tsquery(%) AS q', $get['summary']);
			$where .= ' AND rv.vector @@ q';
		}
		$this->get = $get;

		$region = $_SESSION['role']->getRecord()->id;
        if($this->archive) {
            $this->pager = new Pager($rs->getCountByRegion($region, 'AND show > 0'), (int)@$_GET['start'], $this->itemsPerPage);
        } else {
            $this->pager = new Pager($rs->getCountByRegion($region, 'AND show = 0'), (int)@$_GET['start'], $this->itemsPerPage);
        }
		$this->data = $rs->getListByRegionWithWhere($region, $join, $where, $this->getOrder(), 'LIMIT '.$this->pager->getLimit().' OFFSET '.$this->pager->getCurrent());
	}

	public function show($smarty) {
		$smarty->assign('archive', $this->archive);
		$smarty->assign('get', $this->get);
		$smarty->assign('categories', $this->categories);
        if($this->archive_page) {
            $smarty->assign('pager', $this->pager->getHTML('', 'start', 'sortcol='.$this->sorting['col'].'&amp;sort='.$this->sorting['dir'].'&amp;archive='.$this->archive_page));
        } else {
            $smarty->assign('pager', $this->pager->getHTML('', 'start', 'sortcol='.$this->sorting['col'].'&amp;sort='.$this->sorting['dir']));
        }
		parent::show($smarty);
	}
}

?>