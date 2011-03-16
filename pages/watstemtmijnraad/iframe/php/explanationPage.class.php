<?php
require_once('IndexHandler.class.php');

class explanationPage {
    private $region;
    private $politician;
    private $party;
    private $crumbs;

    private $filter;

    public function processGet($get) {
        if(!isset($get['region']) && !isset($get['party']) && !isset($get['politician'])) {
            Dispatcher::header("/");
        }

        if(isset($get['region']) && $get['region'] != '') {
            $this->filter['region'] = $get['region'];
        }
        if(isset($get['party']) && $get['party'] != '') {
            $this->filter['party'] = $get['party'];
        }
        if(isset($get['politician']) && $get['politician'] != '') {
            $this->filter['politician'] = $get['politician'];
        }
    }

    public function show($smarty) {
        $smarty->assign('filter', $this->filter);
        $smarty->display('explanationPage.html');
    }
}
?>
