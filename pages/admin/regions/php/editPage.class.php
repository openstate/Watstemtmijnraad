<?php

require_once('editPageBase.class.php');
require_once('Region.class.php');
require_once('Level.class.php');

class editPage extends editPageBase {
	private $id;

	public function processGet($get) {
		if(!isset($get['id']) || !ctype_digit($get['id']))
			Dispatcher::header('../');
			
		//[FIXME: fetch object here, otherwise we postpone NotFoundException to show]
		$this->id = $get['id'];
	}

	public function loadFromObject($id) {
		require_once('Region.class.php');
		$obj = new Region();
		$obj->load($id);
		$this->loadData($obj);
	}



	public function saveToObject() {
        if(!$this->data['hidden']) {
            $id = @$this->data['parent'];
            # FIXME: most inefficient traversal. the tree is not prefixed, nor
            # nested set to do it otherwise. this stinks.
            while($id && $rec = Region::loadById($id, false)) {
                # reset hidden flag if any of the parents is hidden
                if($rec['hidden']) {
                    $this->data['hidden'] = true;
                    break;
                }
                $id = $rec['parent'];
            }

        }
		$obj = new Region();
		$this->doSaveToObject($obj);
		$obj->save();

        if(isset($_SESSION['regionID']) && $_SESSION['regionID'] == $obj->id) {
            $_SESSION['regionHidden'] = $obj->hidden;
        }
        
        if($obj->hidden) { # ensure subregions are hidden too
            foreach($obj->selectChildren(true) as $chld) {
                if(!$chld->hidden) {
                    $chld->hidden = true;
                    $chld->save();

                    if(isset($_SESSION['regionID']) && $_SESSION['regionID'] == $chld->id) {
                        $_SESSION['regionHidden'] = $chld->hidden;
                    }
                }
            }
        }
	}


	public function show($smarty) {
		try {
			$this->loadFromObject($this->id);
		} catch (Exception $e) {
			Dispatcher::notFound();
		}
		
		$hasSubs = false;
		$levels = Level::listListOrdered();
		
		$region = new Region();
		$regions = $region->getList();
		
		$result = array();
		$result[] = array(
							'id' => 'null',
							'level' => 1,
							'name' => $levels[1]->name,
						);
		foreach($regions as $region) {
			$result[] = array(
								'id' => $region->id,
								'level' => $region->level + 1,
								'name' => (isset($levels[($region->level + 1)]) ? $levels[($region->level + 1)]->name : ''),
							);			
			if(!$hasSubs && $region->parent == $this->id) $hasSubs = true;
		}
				
		
		$smarty->assign('hasSubs', $hasSubs);
		$smarty->assign('regions', $result);
		$smarty->assign('parents', Region::getDropDownRegionsAll());
	
	
		parent::show($smarty);
	}


	public function action() {
		if($this->data['parent'] == 'null' || $this->data['parent'] == '') $this->data['parent'] = NULL;
		$this->saveToObject();
		Dispatcher::header('../');
	}



}

?>