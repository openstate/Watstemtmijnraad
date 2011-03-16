<?php

require_once('FormPage.class.php');
require_once('Raadsstuk.class.php');
require_once('Submitter.class.php');
require_once('RaadsstukTag.class.php');
require_once('RaadsstukCategory.class.php');
require_once('Category.class.php');

abstract class LoadFormById extends FormPage {
	protected $rs;

	public function processGet($get) {
		try {
			$this->rs = new Raadsstuk($get['id']);

			$ids = Dispatcher::sessionUser()->listSiteIds();
			if(!isset($ids[$this->rs->site_id])) Dispatcher::forbidden();

			if ($this->rs->region != $_SESSION['role']->getRecord()->id)
				Dispatcher::forbidden();
			$this->loadData($this->rs);
		} catch (Exception $e) {
			Dispatcher::forbidden();
		}
		$date = strtotime($this->getRecord()->vote_date);
		$this->council = Council::getCouncilByDate($_SESSION['role']->getRecord()->id, $date);
		
		parent::processGet($get);
	}

	private function loadData($record) {
		$this->data['id'] = $record->id;
		$this->data['title'] = $record->title;
		$this->data['vote_date'] = $record->vote_date;
		$this->data['summary'] = $record->summary;
		$this->data['code'] = $record->code;
		$this->data['type'] = $record->type;
		$this->data['type_name'] = $record->type_name;
		$this->data['submit_type'] = $record->submitter;
		$this->data['submit_type_name'] = $record->submit_type_name;
		$this->data['parent'] = $record->parent;
		$this->data['show'] = $record->show;
		$this->data['site_id'] = $record->site_id;
		$this->data['metainfo'] = $record->metainfo;

		$s = new Submitter();
		$this->data['submitters'] = $s->getSubmittersByRaadsstuk($record->id);

		$t = new RaadsstukTag();
		$this->data['tags'] = array_values($t->getTagsByRaadsstuk($record->id));

		$c = new RaadsstukCategory();
		$this->data['cats'] = $c->getCategoriesByRaadsstuk($record->id);
	}

	protected function getRecord() {
		return $this->rs;
	}
}

?>
