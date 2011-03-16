<?php

abstract class editPageBase {
	protected $data;
	protected $errors = array();
	protected $dataLoaded = false;
	

	public function __construct() {
		$this->clear();
		
		
		$this->errors = array(
			'region_name_0' => false,
			'level_0' => false,
			'level_0' => false,

		);
		
	}

	
	public function clear() {
		$this->data = array(
			'id' => false,
			'region_name' => '',
			'parent' => '',
			'level' => '',
            'hidden' => false,

		);
	}
	

	
	public function null() {
		$this->data = array(
			'id' => false,
			'region_name' => null,
			'parent' => null,
			'level' => null,
            'hidden' => false,

		);
	}

	public function processPost($post) {
		$this->setPost($post);
		if ($this->validate()) { // Success
			$this->dataLoaded = false;
			$this->action();
			return true;
		}
		return false;
	}

	public function setPost($post) {
		$this->null();
		// Conversions from post data to actual values
		// For example, checkboxes use $data[] = isset($post[]);

		// Assignments from post data
		$this->data['id'] = $post['id'];
		if (isset($post['region_name'])) $this->data['region_name'] = $post['region_name'];
		if (isset($post['parent'])) $this->data['parent'] = $post['parent'];
		if (isset($post['level'])) $this->data['level'] = $post['level'];
		if (isset($post['parent'])) $this->data['parent'] = $post['parent'];
		if (isset($post['level'])) $this->data['level'] = $post['level'];
        if (isset($post['level'])) $this->data['hidden'] = @$post['hidden']? 1: 0;

		$this->dataLoaded = true;
	}

	public function validateReduce($prev, $curr) {
		return $prev || $curr;
	}

	public function validate() {
		if (!(isset($this->data['region_name']) && $this->data['region_name']!='')) $this->errors['region_name_0'] = true;
		if (!(isset($this->data['level']) && $this->data['level']!='')) $this->errors['level_0'] = true;
		if (!(isset($this->data['level']) && $this->data['level']!='')) $this->errors['level_0'] = true;

		return !array_reduce($this->errors, array($this, 'validateReduce'), false);
	}
	


	public function loadData($obj) {
		$this->data['id'] = $obj->id;
		$this->data['region_name'] = $obj->name;
		$this->data['parent'] = $obj->parent;
		$this->data['level'] = $obj->level;
        $this->data['hidden'] = $obj->hidden;

	}





	public function doSaveToObject($obj) {
		//[FIXME: this is crazy! provided record $obj'ect is not used if data['id'] is set! ]
		if ($this->data['id'])
			$obj->load($this->data['id']);
		$this->saveProperties($obj);
	}

	public function saveProperties($obj) {
		if (isset($this->data['region_name'])) $obj->name = $this->data['region_name'];
		if (isset($this->data['parent'])) $obj->parent = $this->data['parent'];
		if (isset($this->data['level'])) $obj->level = $this->data['level'];
        if (isset($this->data['hidden'])) $obj->hidden = $this->data['hidden'];
	}


	public function show($smarty) {
		$smarty->assign('formdata',   $this->data);
		$smarty->assign('formerrors', $this->errors);
		
		$smarty->display('editPage.html');
	}
}

?>