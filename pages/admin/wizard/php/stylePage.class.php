<?php

require_once('FileUpload.class.php');
require_once('SkinFactory.class.php');

class StylePage {
	protected $post = array();
	protected $error = array();

	public function __construct() {
		if (!array_key_exists('role', $_SESSION) || !($_SESSION['role'] instanceof BOUserRoleClerk))
			Dispatcher::forbidden();

		$this->region = $_SESSION['role']->getRecord();
	}

	public function processPost($post) {
		if (array_key_exists('prev', $post)) {
				Dispatcher::header('/wizard/politician');
				return;
		}
		
		$this->post = $post;
		if ($_FILES['logo']['error'] == 4) {
			$this->region->used_wizard = 1;
			$this->region->save();
			Dispatcher::header('/raadsstukken');
		}
		elseif (FileUpload::check('logo', array('jpg', 'jpeg', 'png', 'gif'))) {
			try {
				$style = new Style($this->region->id);
			} catch (Exception $e) {
				$style = Style::getDefault();
				$style->setId($this->region->id);
			}

			try {
				$style->logo = FileUpload::storeImage('logo', $_SERVER['DOCUMENT_ROOT'].'/files/', -1, 80);
				$style->save();
				$dir = $_SERVER['DOCUMENT_ROOT'].'/images/watstemtmijnraad/skins/custom/'.$this->region->subdomain; 
				if (!file_exists($dir))
					mkdir($dir);
				$fac = new SkinFactory($this->region->subdomain, array(
					'blu' => $style->color1,
					'grey' => $style->color2,
					'brown' => $style->color3,
					'white' => $style->color4,
					'logo' => '/files/'.$style->logo,
				));
				$fac->generate();
				$this->region->used_wizard = 1;
				$this->region->save();
				Dispatcher::header('/raadsstukken');
			} catch (Exception $e) {
				$this->error['logo_invalid'] = true;
			}
		} else 
			$this->error['logo_invalid'] = true;
		
	}

	public function show($smarty) {
		$smarty->assign('post', $this->post);
		$smarty->assign('error', $this->error);
		$smarty->display('style.html');
	}
}