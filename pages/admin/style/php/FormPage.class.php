<?php

require_once('FormHandler.class.php');
require_once('Image.class.php');
require_once('Style.class.php');
require_once('Validate.class.php');
require_once('GettextPOModule.class.php');
require_once('FileUpload.class.php');
require_once('SkinFactory.class.php');

/**
 * Some of the colors are commented out. Please leave them in, so we can use them later, if needed.
 * By leaving the colors it may be clearer for future developers, that the database and the Record Class already are built for 8 colors
 * The commenting out is needed for handling the form without bugs.
 * @ jan.harkema
 *
 */
abstract class FormPage extends FormHandler {
	protected $pofile;

	public function __construct() {
		if (!array_key_exists('role', $_SESSION) || !($_SESSION['role'] instanceof BOUserRoleClerk))
			Dispatcher::forbidden();

		$this->region = $_SESSION['role']->getRecord();
	
		$this->data = array(
			'id' => null,
			'color1' => null,
			'color2' => null,
			'color3' => null,
			'color4' => null,
			/*'color5' => null,
			'color6' => null,
			'color7' => null,
			'color8' => null,*/
			/*'slogan' => null,
			'font_family' => null,*/
			//'font_color' => null,
			//'font_size' => null,
			//'bg_color' => null,
			'logo' => null,
			//'fields' => null,
		);

		$this->errors = array(
			'color1_invalid' => false,
			'color2_invalid' => false,
			'color3_invalid' => false,
			'color4_invalid' => false,
			/*'color5_invalid' => false,
			'color6_invalid' => false,
			'color7_invalid' => false,
			'color8_invalid' => false,*/
			//'font_color_invalid' => false,
			//'bg_color_invalid' => false,
			//'fields_invalid' => false,
		);
	}

	protected function assign($post) {
		$this->data['color1'] = @trim($post['color1']);
		$this->data['color2'] = @trim($post['color2']);
		$this->data['color3'] = @trim($post['color3']);
		$this->data['color4'] = @trim($post['color4']);
		/*$this->data['color5'] = @trim($post['color5']);
		$this->data['color6'] = @trim($post['color6']);
		$this->data['color7'] = @trim($post['color7']);
		$this->data['color8'] = @trim($post['color8']);*/
		/*$this->data['slogan'] = @trim($post['slogan']);
		$this->data['font_family'] = @trim($post['font_family']);*/
		//$this->data['font_color'] = @trim($post['font_color']);
		//$this->data['font_size'] = @trim($post['font_size']);
		//$this->data['bg_color'] = @trim($post['bg_color']);
		if (isset($post['removeLogo']))
			$this->data['logo'] = -1;
		else if (FileUpload::check('logo', array('jpg', 'png', 'gif', 'bmp'))) {
			$this->data['logo'] = FileUpload::storeImage('logo', $_SERVER['DOCUMENT_ROOT'].'/files/', -1, 80);
		}
		//$this->data['fields'] = @trim($post['fields']);
	}

	/*private function getSize($size) {
		if ($size < 40)
			return 40;
		else if ($size < 96)
			return $size;
		else
			return 96;
	}*/

	protected function validate() {
		if (!Validate::is($this->data['color1'], 'Color')) $this->errors['color1_invalid'] = true;
		if (!Validate::is($this->data['color2'], 'Color')) $this->errors['color2_invalid'] = true;
		if (!Validate::is($this->data['color3'], 'Color')) $this->errors['color3_invalid'] = true;
		if (!Validate::is($this->data['color4'], 'Color')) $this->errors['color4_invalid'] = true;
		/*if (!Validate::is($this->data['color5'], 'Color')) $this->errors['color5_invalid'] = true;
		if (!Validate::is($this->data['color6'], 'Color')) $this->errors['color6_invalid'] = true;
		if (!Validate::is($this->data['color7'], 'Color')) $this->errors['color7_invalid'] = true;
		if (!Validate::is($this->data['color8'], 'Color')) $this->errors['color8_invalid'] = true;
		if (!Validate::is($this->data['font_color'], 'Color')) $this->errors['font_color_invalid'] = true;
		if (!Validate::is($this->data['bg_color'], 'Color')) $this->errors['bg_color_invalid'] = true;*/

		return parent::validate();
	}

	protected function save(Record $r) {
		if (false === $r->id) $r->setId($_SESSION['role']->getRecord()->id);
		$r->color1 = $this->data['color1'];
		$r->color2 = $this->data['color2'];
		$r->color3 = $this->data['color3'];
		$r->color4 = $this->data['color4'];
		/*$r->color5 = $this->data['color5'];
		$r->color6 = $this->data['color6'];
		$r->color7 = $this->data['color7'];
		$r->color8 = $this->data['color8'];*/
		/*$r->slogan = $this->data['slogan'];
		$r->font_family = $this->data['font_family'];*/
		//$r->font_color = $this->data['font_color'];
		//$r->font_size = $this->data['font_size'];
		//$r->bg_color = $this->data['bg_color'];
		if (isset($this->data['logo']))	$r->logo = $this->data['logo'];
		if ($r->logo == -1) $r->logo = null;
		//$r->fields = $this->data['fields'];
		$r->save();

		$dir = $_SERVER['DOCUMENT_ROOT'].'/images/watstemtmijnraad/skins/custom/'.$this->region->subdomain; 
		if (!file_exists($dir))
			mkdir($dir);
		$fac = new SkinFactory($this->region->subdomain, array(
			'blu' => $r->color1,
			'grey' => $r->color2,
			'brown' => $r->color3,
			'white' => $r->color4,
			'logo' => '/files/'.$r->logo,
		));
		$fac->generate();
	}

	private function getPOFile() {
		if (null == $this->pofile)
			$this->pofile = new GettextPOModule('index.po');
		return $this->pofile;
	}

	protected function action() {
		$this->addMessage(Message::SUCCESS, 'success');
		Dispatcher::header('/style');
	}

	protected function error($e) {
		$this->addMessage(Message::ERROR, 'error');
	}

	protected function addMessage($mtype, $type) {
		MessageQueue::addMessage(new Message($mtype, sprintf($this->getPOFile()->getMsgStr('index.'.$type),
															 $this->getPOFile()->getMsgStr('index.action.'.$this->getAction()))));
	}

	abstract protected function getAction();

	public function show($smarty) {
		parent::show($smarty);
		$smarty->display('formPage.html');
	}
}

?>
