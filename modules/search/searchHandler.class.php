<?php

class searchHandler {
	private $params;

	public $urlMap = array(
		'!^/search(/.+)*?/?$!' => 'handler'
	);

	public function handler($match, array $pageSets) {			
		$parts = explode('/', $match[0]);
		$parts = array_slice($parts, 2);
		$get = array();
		for ($i = 0; $i < count($parts); $i += 2) {
			$_GET[$parts[$i]] = @$parts[$i+1];
		}

		$fileName = $_SERVER['DOCUMENT_ROOT'].'/../pages/watstemtmijnraad/search/php/indexPage.class.php';
		$className = 'IndexPage';

		return array(
			'file'  => $fileName,
			'class' => $className,
			'get'   => array()
		);
	}
}

?>