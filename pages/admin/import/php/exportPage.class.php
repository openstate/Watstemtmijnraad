<?php


require_once('ModelSchema.class.php');
require_once('Import.class.php');
require_once('JLogger.class.php');

/** Thrown on any error. */
class ExportException extends Exception { }

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


//ensure we not die somewhere in between
function error_handler($errno, $errstr, $errfile, $errline, $errcontext) {
	throw new Exception($errstr);
}

set_error_handler('error_handler', E_ERROR);



/**
 * Simple webservice front-end for searching/exporting the data.
 * 
 * This page accepts XML queries in following format:
 * 
 * <query indent="true|false">
 *    <time>current timestamp</time>
 *    <region name="name" path="/path/to/region" />
 *    <category>name</category>
 *    <tag>name</tag>
 *    <party>name</party>
 *    <voted from="date" to="date" />
 *    <code>code</code>
 *    <title>keywords</title>
 *    <summary>keywords</summary>
 * </query>
 * 
 * Where all elements except the time are optional and may be specified multiple times,
 * the resulting set will be union of all subsets.
 * 
 * The time element is required, it should contain the current time (not older than
 * EXPORT_SIGNATURE_TIMEOUT). One GET parameter is required $_GET['sig'], which should
 * be a string containig the signature of the whole query.
 */
class exportPage {
	
	public function processGet($get) {
		header("Content-Type: text/xml");
		header("Content-Disposition: attachment; filename='export.xml'");
				
		//output stream
		$xw = new XMLWriter();
		$xw->openUri('php://output');
		$xw->startDocument('1.0','UTF-8');
		
		try {
			if (@$_SERVER['REQUEST_METHOD'] != 'POST')
				throw new ExportException('Unsupported request method');
		
			// authenticate user
			$user = DBs::inst(DBs::SYSTEM)->query('SELECT * FROM usr_export_users WHERE name = %', @$get['user'])->fetchRow();
			if (!$user) throw new ExportException('Invalid signature');
			
			/* Fetch xml input */
			$input = file_get_contents('php://input');
			if(!isset($get['sig']) || $input == '') throw new ExportException('Invalid signature');
			
			/* Verify the RSA signature sent */
			$pub = openssl_get_publickey($user['key']);
			$sig = pack('H*', $get['sig']);
			$valid = openssl_verify($input, $sig, $pub);
			openssl_free_key($pub);
			if (!$valid) throw new ExportException('Invalid signature');
			
			
			//parse query
			$query_xml = simplexml_load_string($input);
			if(!$query_xml) throw new ExportException('XML validation failed');
			
			//check if query is fresh
			if(intval((string)$query_xml->time) + EXPORT_SIGNATURE_TIMEOUT < time())
				throw new ExportException('Query is outdated');
			
			
			//access to any region
			// BIG FAT WARNING: if a raadsstuk has some other raadsstuk as parent, which is not in
			// list of allowed regions, then it will be sent anyway, otherwise the export file will
			// be broken!
			if($user['region_access'] == '*') $regions = null;
			else {
				$regions = array_map('intval', explode(',', trim($user['region_access'])));
				if(sizeof($regions) < 1) throw new ExportException('User has no access to any region');
			}
			$regions = null;
			
			// parse query
			$indent = ((string)$query_xml['indent'] == "false")? false: true; //includes NULL
			$xw->setIndent($indent);
			$site = 'Watstemtmijnraad'; //for security reasons can not be specified

  			//OK, fetch the data
			$db = getPDOConnection();
			$db->beginTransaction();
			define('DRY_RUN', true); //ensure no bug changes data
			
			//document prelude
    		$xw->startDtd('wsmr', null, 'http://watstemtmijnraad.nl/import.dtd');
    		$xw->endDtd();
    
    		$xw->startElement('import');
    		$xw->writeAttribute('version', '1.0');
    		$xw->writeAttribute('site', $site);	
    			
			try {
				$query = new ExportQueryBuilder();
				
				$region_names = array();
				foreach($query_xml->region as $region) $region_names[] = (string)$region;
			
    			//load whole schema
    			$schema = new ModelSchema($db);
    			//filter on allowed regions
    			if($region_names) { //filter on regions
    				$regschema = $schema->getRegionSchema();
    				$regs = array();
    				foreach ($region_names as $reg) {
    					try {
    						$regs[] = $regschema->getRegion($reg)->getId();
    					} catch (Exception $e) { //modification exception
    						//ignore unknown region or attempt to insert new region (implicit creation)
    					}
    				}
    				
    				//filter on regions, restricted access
    				if($regions) $regs = array_intersect($regs, $regions);
    				if(!$regs) $query->emptySet(); //intersection is empty
    				
    				//else filter on regions, full access
    			} elseif($regions) $regs = $regions; //no filter on regions, no full acces
    			  else $regs = null; //no filter on regions, full access
    			  
    			if($regs != null) foreach ($regs as $regid) $query->region($regid);
    			
    			$categories = $schema->getCategorySchema();
    			foreach ($query_xml->category as $cat) {
    				try {
    					$cat = $categories->getCategory((string)$cat);
    					$query->category($cat->getId());
    				} catch (Exception $e) {
    					//ignore modification (dry-run)
    				}
    			}
    			
    			$tags = $schema->getTagSchema();
    			foreach ($query_xml->taq as $tag) {
    				try {
    					$tag = $tags->getTag((string)$tag);
    					$query->tag($tag->getId());
    				} catch (Exception $e) {
    					//ignore modification (dry-run)
    				}
    			}
    			
    			$parties = $schema->getPartySchema();
    			foreach ($query_xml->party as $party) {
    				try {
    					$party = $parties->getParty((string)$party);
    					$query->party($party->getId());
    				} catch (Exception $e) {
    					//ignore modification (dry-run)
    				}
    			}
			
    			foreach ($query_xml->voted as $voted) {
    				$from = $voted['from']? ModelSchema::normalizeDate((string)$voted['from']): null;
    				$to = $voted['to']? ModelSchema::normalizeDate((string)$voted['to']): null;
    				$query->voteDate($from, $to);
    			}
			
    			foreach ($query_xml->code as $code) $query->code((string)$code);
    			foreach ($query_xml->title as $title) $query->title((string)$title);
    			foreach ($query_xml->summary as $summary) $query->summary((string)$summary);
    			
    			
    			//write out full schema
				$options = array('regions.flat' => true, 'politician.real-id' => true);
    			$schema->toXmlWrite($xw, $options);
    			
    			//select and write out raadsstukken
    			RaadsstukModel::toXmlWrite($schema, $db, $xw, $site, $query);
    
    			//little protection from bugs
				$db->rollBack();
				unset($schema);
			} catch (Exception $e) {
				$db->rollBack();
				
				$error = $e->getMessage();
				$xw->writeElement('error', $error);
			}
			
			//finish document (we could send invalid document here, since we don't know how many tags are open!)
        	$xw->endElement(); // </import>
		} catch (Exception $er) {
			//XML writer can't fail
			$error = $er->getMessage();
			$xw->writeElement('error', $error);
		}
		
		$xw->endDocument();
		$xw->flush();
		unset($xw);
	}
}

?>