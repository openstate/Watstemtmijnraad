<?php

require_once('ModelSchema.class.php');
require_once('Import.class.php');
require_once('JLogger.class.php');

class ImportException extends Exception { }

function getPDOConnection() {
	static $pdo = null;

	if($pdo == null) {
		$inf = require('database.private.php');
		$inf = $inf[DBs::SYSTEM];

		$config = array(
			'port' => 5432,
			'host' => $inf['host'],
			'user' => $inf['user'],
			'password' => $inf['pass'],
			'database' => $inf['database']
		);

		$pdo = new PDO("pgsql:host={$config['host']} port={$config['port']} dbname={$config['database']}", $config['user'], $config['password']);
		//$pdo->query("SET NAMES 'utf8'"); //[XXX: needed for MySQL 5.x on my machine, comment out if causes problems ]
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	return $pdo;
}


function error_handler($errno, $errstr, $errfile, $errline, $errcontext) {
	throw new Exception($errstr);
}

set_error_handler('error_handler', E_ERROR);


class IndexPage {
	public function processGet($get) {
		$error = false;
		$ids = array();
		
		ob_start();
		try {
			if (@$_SERVER['REQUEST_METHOD'] != 'POST')
				throw new ImportException('Unsupported request method');
		    
			
			/* Fetch xml input */
			$input = file_get_contents('php://input');
				
			/* Content type should be text/xml */
			if (@$_SERVER['CONTENT_TYPE'] != 'text/xml')
				throw new ImportException('Unsupported content type');
				
			$user = DBs::inst(DBs::SYSTEM)->query('SELECT * FROM usr_import_users WHERE name = %', @$get['user'])->fetchRow();
			if (!$user)
				throw new ImportException('Invalid signature');
			
			
			/* Verify the RSA signature sent */
			$pub = openssl_get_publickey($user['key']);
			$sig = pack('H*', $get['sig']);
			$valid = openssl_verify($input, $sig, $pub);
			openssl_free_key($pub);
			if (!$valid)
				throw new ImportException('Invalid signature');

			
			$pdo = getPDOConnection();
			$pdo->beginTransaction();
			
			//has user access to any region?
			define('FULL_ACCESS', $user['region_list'] == '*');
			if($user['region_list'] == '') throw new ImportException('No access to any region');
			elseif(!FULL_ACCESS) {
				define('REGION_ACCESS', true);
				$GLOBALS['REGION_ACCESS_LIST'] = array_flip(array_map('intval', array_map('trim', explode(',', $user['region_list']))));
			}
			
			
			try {
				$import = new Import($pdo);
				$import->setStringSource($input);
				$import->process(false, $ids);
				$pdo->commit();
			} catch (Exception $e) {
				$pdo->rollBack();
				throw $e;
			}

		} catch (Exception $e) {
			#if ($e instanceof ImportException || $e instanceof RuntimeException)
				$error = $e->getMessage();
			#else
			#	$error = 'Internal server error';
		}
		
		//if logging is configured improperly, we echo this
		$cont = ob_get_clean();
		
		header('Content-Type: text/xml');
		$xw = new XMLWriter();
		$xw->openUri('php://output');
		$xw->setIndent(true);
		$xw->startDocument('1.0','UTF-8');
		
		$xw->startElement('result');
    	$xw->writeAttribute('status', $error? 'error': 'OK');
    	
    	if($error) $xw->writeElement('error', $error);
    	elseif($ids) {
    		$host = 'http://'.$_SERVER['HTTP_HOST'];
    		$xw->startElement('idmaps');

    		foreach ($ids as $refid => $ourid) {
    			$xw->startElement('raadsstuk');
    			$xw->writeAttribute('refid', $refid);
    			$xw->writeAttribute('id', $ourid);
    			$xw->text("{$host}/raadsstukken/raadsstuk/{$ourid}");
    			$xw->endElement();
    		}
    		
    		$xw->endElement();
    	}
    	
    	if($cont) $xw->writeElement('log', $cont);
		
    	$xw->endElement();
    	
		$xw->endDocument();
		$xw->flush();
		unset($xw);
	}

}

?>