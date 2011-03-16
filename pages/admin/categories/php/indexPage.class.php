<?php

require_once('indexPageBase.class.php');
require_once('RaadsstukCategory.class.php');
require_once('Appointment.class.php');
require_once('Level.class.php');

class indexPage extends indexPageBase {
	protected $sortDefault = 'name';
    private $category_counts;
    private $category_levels;




	public function show($smarty) {
		if (isset($_SESSION['error'])) {
			$smarty->assign('error', $_SESSION['error']);
			unset($_SESSION['error']);
			if(isset($_SESSION['apps_del'])) {
				$smarty->assign('apps_del', $_SESSION['apps_del']);
				unset($_SESSION['apps_del']);
			}
		}

        $smarty->assign('levels', $this->category_levels);
        $smarty->assign('counts', $this->category_counts);
		parent::show($smarty);
	}

    public function processGet($smarty) {
        $this->loadFromObject();
        $category_ids = array();
        $c = new RaadsstukCategory();
        foreach($this->data as $category) {
            $category_ids[] = $category['id'];
            $this->category_counts[$category['id']] = $c->countByCategory($category['id']);
        }

        $db = DBs::inst(DBs::SYSTEM);
        $c_levels = $db->query('SELECT l.*, cr.* FROM sys_levels l JOIN sys_category_regions cr ON l.id = cr.level AND cr.category IN ('. implode(', ', $category_ids) .  ') ORDER BY l.id')->fetchAllRows();

        foreach($c_levels as $level) {
            $this->category_levels[$level['category']][] = $level['name'];
        }

    }

}

?>