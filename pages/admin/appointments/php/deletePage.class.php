<?php

require_once('LoadFormById.class.php');
require_once('Politician.class.php');
require_once('Vote.class.php');

class DeletePage extends LoadFormById {
	public function processPost($post) {
		try {
			if(isset($post['cancel'])) parent::processPost($post); //handles cancel redirect
			
			$app = $this->getRecord();
			if ($app->isExpired()) {
				$app->delete(); //triggers will do the clean up job
			} else { //now() between function range
				// do not really delete, but end appointment immediately
				$app->time_end = date('Y-m-d H:i:s');
				$app->save(); //trigger will optionaly clean votes
			}
		} catch (Exception $e) {
			$this->error($e);
			return;
		}
		$this->action();
	}

	protected function loadData($record) {
		$this->data['politician_name'] = $this->politician->formatName();
		parent::loadData($record);
	}

	protected function getFormParameters() {
		$app = $this->getRecord();
		if ($app->isExpired()) {
			$count = Vote::countObsolete($this->getRecord()->politician, $this->getRecord()->id);
		} else {
			$count = Vote::countObsolete($this->getRecord()->politician, $this->getRecord()->id, $this->getRecord()->time_start, date('Y-m-d H:i:s'));
		}
		
		return array('name' => 'AppointmentDelete',
								 'header' => 'Aanstelling verwijderen',
								 'note' => ($app->isExpired() ? 
															'Weet u zeker dat u de onderstaande verlopen aanstelling wilt verwijderen?' :
															'Weet u zeker dat u de onderstaande aanstelling wilt laten verlopen?').
															($count > 0? "<br><br><b style='color: red'>Waarschuwing:</b> <b>{$count}</b> ".($count > 1? 'stemmen worden': 'stem wordt').' verwijderd.': ''),
								 'submitText' => ($count > 0? 'Toch verwijderen': 'Verwijderen'),
								 'freeze' => true,
								 'showPolitician' => true);
	}

	protected function getAction() {
		return 'delete';
	}
	
	private function mdate($date) {
		return $date == 'infinity' || $date == '-infinity'? 'onbepaald': date("j F Y", strtotime($date));
	}
}

?>
