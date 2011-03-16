<?php

class noRightsPage {
	private $urlExtension;

	public function processGet($get) {
		$this->urlExtension = @$get['destination'];
	}

	public function show($smarty) {
		$smarty->assign('url', $this->urlExtension);
		$smarty->display('noRightsPage.html');
	}
}

?>
