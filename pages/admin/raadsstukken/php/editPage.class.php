<?php

require_once('LoadFormById.class.php');
require_once('Raadsstuk.class.php');

class EditPage extends LoadFormById {
    protected $active_parties;
    protected $selected_party;
    protected $ext_url_info;
    
	protected function getFormParameters() {
		return array('name' => 'RaadsstukEdit',
								 'header' => 'Raadsstuk wijzigen',
								 'objectId' => $this->rs->id,
								 'submitText' => 'Wijzigen',
								 'extraButton' => 'Wijzigen en naar stemming');
	}

	protected function getAction() {
		return 'edit';
	}

    public function processGet($get) {
        $rs = new Raadsstuk($get['id']);
        $this->ext_url_info = $rs->ext_url_info;
        $this->selected_party = $rs->party;
        $parties = Party::listActiveParties($_SESSION['role']->getRecord()->id);

        $this->active_parties[0] = "- Kies een partij -";
        foreach($parties as $party) {
            $this->active_parties[$party->id] = $party->name;
        }
        
        parent::processGet($get);
    }

	public function show($smarty) {
        $smarty->assign('ext_url_info', $this->ext_url_info);
        $smarty->assign('selected_party', $this->selected_party);
        $smarty->assign('list_parties', $this->active_parties);
		$smarty->assign('cats', json_encode(array_keys($this->data['cats'])));
		$smarty->assign('catNames', json_encode(array_values($this->data['cats'])));
		$smarty->assign('tags', json_encode($this->data['tags']));
		parent::show($smarty);
	}
}

?>
