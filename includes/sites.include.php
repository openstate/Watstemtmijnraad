<?php

$backoffice = array(
		'sets'       => array('admin', 'shared'),
		'modules'    => array('secure', 'crumbs', 'dbpages', 'user'),
		'title'      => 'Wat Stemt Mijn Raad Backoffice',
		'template'   => 'backoffice.html',
		'publicdir'  => 'backoffice',
		'systemMail' => 'no-reply@watstemtmijnraad.nl',

		'locale' => array(
			'source'   => array('cookie', 'browser'),
			'locales'  => array('nl'),
			'defaults' => array(
				'/\.nl$/'     => 'nl',
				'/\.accepteproject.nl$/'     => 'nl',
				'/\.gl$/'     => 'nl',
				'/\.dev(elop)?$/'    => 'nl',
			)
		)
	);

$frontoffice = array(
		'sets'       => array('watstemtmijnraad', 'shared'),
		'modules'    => array('search', 'default', 'crumbs', 'dbpages', 'user', 'ajax', 'xml', 'hnsdev'),
		'title'      => 'Wat Stemt Mijn Raad',
		'template'   => 'watstemtmijnraad.html',
		'publicdir'  => 'watstemtmijnraad',
		'systemMail' => 'no-reply@watstemtmijnraad.nl',

		'locale' => array(
			'source'   => array('cookie'),
			'locales'  => array('nl'),
			'defaults' => array(
				'/\.nl$/'    => 'nl',
				'/\.accepteproject.nl$/'     => 'nl',
				'/\.gl$/'    => 'nl',
				'/\.dev(elop)?$/'   => 'nl',
			)
		)
	);

return array(
	'/^(?P<subdomain>backoffice)\.(?P<domain>watstemtmijnraad)\.(?P<tld>[^.]+)$/' => $backoffice,
	'/^(?P<subdomain>backoffice)\.(?P<domain>watstemtmijnraad)\.(?P<tld>accepteproject\.[^.]+)$/' => $backoffice,
	'/^(?P<subdomain>([^\.]*))?\.?(?P<domain>watstemtmijnraad)\.(?P<tld>[^.]+)$/' => $frontoffice,
	'/^(?P<subdomain>([^\.]*))?\.?(?P<domain>watstemtmijnraad)\.(?P<tld>accepteproject\.[^.]+)$/' => $frontoffice,
);

?>
