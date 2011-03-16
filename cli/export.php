<?php
/**
* Export data from database to import file.
*
* @author Sardar Yumatov (ja.doma@gmail.com)
*/

//setup environment
require_once(dirname(__FILE__).'/../includes/prequel.cli.php');

require_once('ModelSchema.class.php');
require_once('Import.class.php');
require_once('JLogger.class.php');


$file = null;
$from = null;
$to = null;
$schema_mode = 'dependent';
$site = 'Watstemtmijnraad';
$regions = null;
$region_names = null;

for($i = 1; $i < sizeof($argv); $i++) {
	if($argv[$i][0] == '-') {
		$gonext = false;
		switch ($argv[$i]) { //no argument options
			case '-help':
			case '--help': $file == null; $i = sizeof($argv); break;
			case '-sd': $schema_mode = 'dependent'; break;
			case '-sf': $schema_mode = 'full'; break;
			case '-so': $schema_mode = 'nodata'; break;
			default: $gonext = true;
		}
		
		if($gonext) {
			if($i + 1 >= sizeof($argv)) {
				echo "Error: expecting argument after {$argv[$i]}\n";
				$file = null;
				break;
			}
			
			switch ($argv[$i]) {
				case '-f':
				case '-from': if(($from = ModelSchema::normalizeDate($argv[$i + 1])) === false) {
									echo "Error: can't recognize start date: {$argv[$i + 1]}. Expecting yyyy-mm-dd format.\n";
									$file = null;
									$i = sizeof($argv);
									break;
							  } else {
							  	 $i += 1;
							  	 break;
							  }
							  
				case '-t':
				case '-to': if(($to = ModelSchema::normalizeDate($argv[$i + 1])) === false) {
								echo "Error: can't recognize end date: {$argv[$i + 1]}. Expecting yyyy-mm-dd format.\n";
								$file = null;
								$i = sizeof($argv);
								break;
							} else {
								$i += 1;
								break;
							}
							
				case '-s':
				case '-site': $site = $argv[$i + 1];
							  $i += 1;
							  break;
				
				case '-r':
				case '-regions': $regions = $argv[$i + 1];
								 $region_names = array_map('ucfirst', array_map('strtolower', array_map('trim', explode(',', $regions))));
								 $regions = implode(', ', $region_names);
								 $i += 1;
						  		 break;
						  		 
				default: echo "Non-recognized option: {$argv[$i]}.";
				         $file = null;
				         $i = sizeof($argv);
			}
		}
	} else $file = $argv[$i];
}

//check arguments
if($file == null) {
	echo "Usage: php export.php [-from <date>] [-to <date>] <filename>\n";
	echo "    -from date    -- fetch raadsstukken from this date yyyy-mm-dd\n";
	echo "    -to   date    -- fetch raadsstukken up to this date yyyy-mm-dd\n";
	echo "    -sd           -- export only used schema parts. This is the default.\n";
	echo "    -sf           -- export whole schema.\n";
	echo "    -so           -- do not export data, generate schema only file.\n";
	echo "    -site name    -- fetch data from specific site. Default 'Watstemtmijnraad'\n";
	echo "    -regions name -- limit to specific (,) separated list of regions\n";
	exit(0);
}


echo "Exporting: {$site} ".($schema_mode == 'dependent'? 'dependent schema and data': ($schema_mode == 'nodata'? 'schema only': 'full schema and data'))." within range [".
				   (($from == null && $to == null)? 'any data': ($from == null? "up to {$to}": ($to == null? "{$from} to present": "{$from} to {$to}"))).
				   "] ".($regions != null? "of regions: {$regions}": '')." to file {$file}\n";



if(!defined('JLOGGER_FILE_BASE')) echo "Warning: JLOGGER_FILE_BASE is not defined, file logger will probably fail.\nWarning: define JLOGGER_FILE_BASE in privates/settings.private.php\n";

$root = JLogger::getLogger();
$root->addLogHandler(new EchoJLoggerHandler());
//$root->setLogLevel(JLogger::LEVEL_NOTICE);


//============================- Export procedure -============================
$db = getPDOConnection();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);
		
$db->beginTransaction();

//ensure no buggy writes
define('DRY_RUN', true);

//schema export options
$options = array('regions.flat' => true, 'politician.real-id' => true);

try {
	$xw = new XMLWriter();
	if(!$xw->openUri($file)) {
		throw new RuntimeException("Can't open output file: {$file}");
	}
	$xw->setIndent(true);
	
	$xw->startDocument('1.0','UTF-8');
    $xw->startDtd('wsmr', null, 'http://watstemtmijnraad.gl/import.dtd');
    $xw->endDtd();
    
    $xw->startElement('import');
    $xw->writeAttribute('version', '1.0');
    $xw->writeAttribute('site', $site);
    
    $schema = new ModelSchema($db); //fetch all the data
    if($region_names != null) {
    	$regschema = $schema->getRegionSchema();
    	$regs = array();
    	foreach ($region_names as $reg) {
    		try {
    			$regs[] = $regschema->getRegion($reg)->getId();
    		} catch (Exception $e) { //modification exception
    			throw new RuntimeException("Region: {$reg} is not found!", $e);
    		}
    	}
    } else $regs = null;
    
    //switched to query builder
    $qb = new ExportQueryBuilder();
    if($regs != null) foreach ($regs as $regid) $query->region($regid);
    if($from || $to) $query->voteDate($from, $to);
    	
    switch ($schema_mode) {
    	case 'nodata': $schema->toXmlWrite($xw, $options);
    	               break;
    	
    	case 'full': $schema->toXmlWrite($xw, $options);
    				 RaadsstukModel::toXmlWrite($schema, $db, $xw, $site, $qb);
    				 break;
    	
    	default: $schema->startDependencyTrace();
    			 $xwt = new XMLWriter();
				 if(!$xwt->openUri($file."_temp")) {
					throw new RuntimeException("Can't create temp output file: {$file}_temp");
				 }
				 $xwt->setIndent(true);
				 $xwt->startDocument('1.0','UTF-8');
				 $xwt->startElement('import');
				 
    		     RaadsstukModel::toXmlWrite($schema, $db, $xwt, $site, $qb);
    		     
    		     $xwt->endElement(); // </import>
    		     $xwt->endDocument();
			     $xwt->flush();
			     unset($xwt);
			     
    		     //write touched schema
    		     $schema->toXmlWrite($xw, $options);
    		     
    		     //merge files
    		     $dat = new XMLReader();
    		     if(!$dat->open($file."_temp")) {
    		     	throw new RuntimeException("Wow! Can't open data xml file that I've just created. Something is really wrong and I don't know what =/");
    		     }
    		     
    		     $dat->read(); //to import element
    		     $xmldata = $dat->readInnerXml();
    			 $dat->close(); 
    		     $xw->writeRaw(ltrim($xmldata));
    		     unlink($file."_temp");
    		     break;
    }
    
    $xw->endElement(); // </import>
	$xw->endDocument();
	$xw->flush();
	
	unset($schema);
	unset($xw);
	
	$db->rollBack(); //little protection from bugs
} catch (Exception $e) {
	$db->rollBack();
	//if($e instanceof DatabaseException) $root->error("Database failure: ".$e->getMessage(), $e);
	echo "\nError: ".$e;
	echo "\nAll changes rolled back.";
}

unset($db);
echo "\nDone.";
?>