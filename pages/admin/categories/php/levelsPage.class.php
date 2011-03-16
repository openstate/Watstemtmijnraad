<?php

require_once('Category.class.php');
require_once('Level.class.php');

/** Binds categories to specific region levels. */
class LevelsPage {
	protected $category;
	protected $message;


	public function processGet($get) {
		if (!isset($get['id']) || !ctype_digit($get['id'])) Dispatcher::header('../');
		try {
			$this->category = new Category(intval($get['id']));
		} catch (Exception $e) {
			Dispatcher::notFound();
		}
	}

	public function processPost($post) {
		$levdescr = array();
		foreach ($post['check'] as $key => $chk) {
			$levdescr[$key] = $post['desc'][$key];
		}

		try {
			$this->category->setLevelDescriptions($levdescr);
			$this->message = 'De wijzigingen zijn doorgevoerd.';
		} catch (Exception $e) {
			$this->message = 'Er is een fout opgetreden.';
		}
	}

	public function show($smarty) {
		$smarty->assign('levels', $this->category->listLevelDescriptions());
		$smarty->assign('category', $this->category);
		if (strlen($this->message) > 0) $smarty->assign('message', $this->message);
		$smarty->display("levelsPage.html");
	}
}

?>