<?php

require_once('FormPage.class.php');
require_once('Raadsstuk.class.php');
require_once('Party.class.php');

class CreatePage extends FormPage {
	protected $toVotes;
	protected $id;

	protected $rs = null;
    protected $active_parties;

	public function processGet($get) {
		$this->data['type'] = 1;
		$this->data['submit_type'] = 1;

        $parties = Party::listActiveParties($_SESSION['role']->getRecord()->id);
        $this->active_parties[0] = "- Kies een partij -";
        foreach($parties as $party) {
            $this->active_parties[$party->id] = $party->name;
        }

		parent::processGet($get);
	}

	protected function getRecord() {
		if (!$this->rs) {
			$this->rs = new Raadsstuk();
			$this->rs->show = 0;
		}
		return $this->rs;
	}

	protected function getFormParameters() {
		return array('name' => 'RaadsstukCreate',
								 'header' => 'Nieuw raadsstuk',
								 'submitText' => 'Toevoegen',
								 'extraButton' => 'Stemming invoeren');
	}

	protected function getAction() {
		return 'create';
	}

	protected function save(Record $r) {
		$r->result = Raadsstuk::NOTVOTED;
		parent::save($r);
	}

	public function show($smarty) {
		$smarty->assign('cats', '[]');
		$smarty->assign('catNames', '[]');
		$smarty->assign('tags', '[]');
        $smarty->assign('selected_party', 0);
        $smarty->assign('list_parties', $this->active_parties);
		$smarty->assign('create', true);
		parent::show($smarty);
	}
}

?>
