<?php

require_once('LoadFormById.class.php');

class EditPage extends LoadFormById {
	protected function getFormParameters() {
		return array('name' => 'PageEdit',
								 'header' => 'Pagina wijzigen',
								 'submitText' => 'Wijzigen');
	}

	protected function getAction() {
		return 'edit';
	}

	public function show($smarty) {
		//[FIXME: Region id 2 = Nederland, directly used!]
		if (null == $this->getRecord()->region && $_SESSION['role']->getRecord()->id != 2){ //Only edit pages when you are in Nederland..? :/
			$smarty->assign('target',  '/pages/create');
		}
		parent::show($smarty);
	}
}

?>
