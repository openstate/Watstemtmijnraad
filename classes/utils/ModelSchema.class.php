<?php

require_once('RegionModelSchema.class.php');
require_once('CategoryModelSchema.class.php');
require_once('TagModelSchema.class.php');
require_once('SiteModelSchema.class.php');

require_once('PartyModelSchema.class.php');
require_once('PoliticianModelSchema.class.php');

require_once('JLogger.class.php');


/*
 * Stem settings. Defining the stem option will disable stemming, in that case trim:lowercase
 * only will be applied to the key.
 *
 * In case you don't know what stemming is, compare: "Fryslân" and "Fryslan"
 *   - enable stem  -- they are equal
 *   - disable stem -- they are distinct
 */
//define('DISABLE_STEM', true);				//Disable stemming everywhere
//define('DISABLE_STEM_REGION', true);		//Disable stemming for region names
//define('DISABLE_STEM_CATEGORY', true);	//Disable stemming for category names
//define('DISABLE_STEM_SITE', true);		//Disable stemming for site names
//define('DISABLE_STEM_TAG', true);			//Disable stemming for tags
//define('DISABLE_STEM_PARTY', true);		//Disable stemming for parties




/**
* Watstemtmijnraad.nl database schema.
*
* @author Sardar Yumatov (ja.doma@gmail.com)
*/
class ModelSchema {

	private $db;

	/** @var RegionModelSchema */
	private $region;

	/** @var CategoryModelSchema */
	private $categories;

	/** @var TagModelSchema */
	private $tags;

	/** @var SiteModelSchema */
	private $sites;

	/** @var PartyModelSchema */
	private $party;

	/** @var PoliticianModelSchema */
	private $politicians;

	/**
	 * Load "watstemtmijnraad.nl" schema.
	 *
	 * @throws RuntimeException on any error
	 * @param PDO $db database access
	 */
	public function __construct($db) {
		$log = JLogger::getLogger('util.import.schema');
		$log->enter("Initializing all schema's");

		$this->db = $db;
		$this->region = new RegionModelSchema($db, $this);
		$this->categories = new CategoryModelSchema($db, $this);
		$this->tags = new TagModelSchema($db, $this);
		$this->sites = new SiteModelSchema($db, $this);

		$this->party = new PartyModelSchema($db, $this);
		$this->politicians = new PoliticianModelSchema($db, $this);

		$log->leave("All schema's are successfully loaded");
	}


	/** Start tracing all queries to this schema. */
	public function startDependencyTrace() {
		$this->region->startDependencyTrace();
		$this->categories->startDependencyTrace();
		$this->party->startDependencyTrace();
		$this->politicians->startDependencyTrace();
	}
	
	/**
	 * Returns region schema.
	 * @return RegionModelSchema
	 */
	public function getRegionSchema() {
		return $this->region;
	}


	/**
	 * Returns party schema.
	 * @return PartyModelSchema
	 */
	public function getPartySchema() {
		return $this->party;
	}

	/**
	 * Returns category schema.
	 * @return CategoryModelSchema
	 */
	public function getCategorySchema() {
		return $this->categories;
	}

	/**
	 * Returns tag schema.
	 * @return TagModelSchema
	 */
	public function getTagSchema() {
		return $this->tags;
	}

	/**
	 * Returns site schema.
	 * @return SiteModelSchema
	 */
	public function getSiteSchema() {
		return $this->sites;
	}

	/**
	 * Returns
	 * @return PoliticianModelSchema
	 */
	public function getPoliticianSchema() {
		return $this->politicians;
	}

	/**
	 * Serialize this schema to the DOM tree.
	 *
	 * @param DOMDocument $dom the owner document, used to create elements
	 * @param DOMElement $root where to 'schema' element will be added
	 * @param array $options extra options
	 * @return void
	 */
	public function toXml($dom, $root, $options = null) {
		$el = $dom->createElement('schema');
		$root->appendChild($el);

		$this->region->toXml($dom, $el, $options);
		$this->categories->toXml($dom, $el, $options);
		$this->party->toXml($dom, $el, $options);
		$this->politicians->toXml($dom, $el, $options);
	}

	/**
	 * Serialize this schema to XML stream.
	 *
	 * @param XMLWriter $xw XML output stream
	 * @param array $options extra options
	 * @return void
	 */
	public function toXmlWrite($xw, $options = null) {
		$xw->startElement('schema'); // <schema>
		
		$this->region->toXmlWrite($xw, $options);
		$this->categories->toXmlWrite($xw, $options);
		$this->party->toXmlWrite($xw, $options);
		$this->politicians->toXmlWrite($xw, $options);
		
		$xw->endElement(); // </schema>
	}

	/**
	 * Read & update schema from XML data.
	 *
	 * @throws RuntimeException on any error
	 * @param SimpleXMLElement $node schema node
	 * @return void
	 */
	public function update(SimpleXMLElement $node) {
		$log = JLogger::getLogger('util.import.schema');
		$log->enter("Begin with updating all schema's from XML source.");

		foreach ($node->children() as $chld) {
			switch ($chld->getName()) {
				case 'regions':
					$this->region->update($chld);
					break;

				case 'categories':
					$this->categories->update($chld);
					break;

				case 'parties':
					$this->party->update($chld);
					break;

				case 'politicians':
					$this->politicians->update($chld);
					break;

				default: throw new RuntimeException("Unknown schema container: ".$chld->getName());
			}
		}

		$log->leave("Successfully updated all schema's from XML source.");
	}
	
	
	/**
	 * Just converts $key to lowercase.
	 * 
	 * BIG FAT WARNING: the $key is expected to be in UTF-8.
	 *
	 * @param string $key the string to convert
	 * @return string converted string
	 */
	public static function plainNormalize($key) {
		$key = (string)$key;
		if(!mb_check_encoding($key, 'UTF-8')) {
			throw new InvalidArgumentException("The given string is not in expected encoding(" . mb_internal_encoding() . "). Please, set mbstring.internal_encoding in php.ini to encoding of this stirng: {$key}");
		}
		
		return mb_strtolower($key, 'UTF-8');
	}
	
	/**
	 * Generic 'accent' remover.
	 * Method removes accent charactes from the input string, unless DISABLE_STEM is defined.
	 *
	 * BIG FAT WARNING: the $key is expected to be in UTF-8. This source file must be in UTF-8 too!.
	 * 
	 * @param string $key the key to convert
	 * @return string processed key
	 */
	public static function normalize($key) {
		$key = self::plainNormalize($key);
		if(!defined('DISABLE_STEM')) {
			//source must be in UTF, $pat's are 2 byte strings
			$pat = array('À','Á','Â','Ã','Ä','Å','Æ','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ð','Ñ','Ò','Ó','Ô','Õ','Ö','Ø','Ù','Ú','Û','Ü','Ý','Þ','ß','à','á','â','ã','ä','å','æ','ç','è','é','ê','ë','ì','í','î','ï','ð','ñ','ò','ó','ô','õ','ö','ø','ù','ú','û','ý','ý','þ','ÿ','Ŕ','ŕ');
			$rep = array('a','a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','d','n','o','o','o','o','o','o','u','u','u','u','y','b','s','a','a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','d','n','o','o','o','o','o','o','u','u','u','y','y','b','y','R','r');
			//will work since we do binary byte comparison
			$key = str_replace($pat, $rep, $key, $suka);
		}
		return $key;
	}
	
	/**
	 * Accepts 'dd-mm-yyyy' or 'yyyy-mm-dd' date and converts this to 'yyyy-mm-dd'.
	 * @param string $str_date user input date
	 * @return mixed null - infinity, date as 'yyyy-mm-dd' or false if string is not recognized
	 */
	public static function normalizeDate($str_date) {
		$str_date = trim((string)$str_date);
		if($str_date == '' || $str_date == 'infinity' || $str_date == '-infinity') return null;
		if(preg_match('#^([0-9]{4})[^0-9]([0-9]{2})[^0-9]([0-9]{2})$#', $str_date, $mth)) return "{$mth[1]}-{$mth[2]}-{$mth[3]}";
		if(preg_match('#^([0-9]{2})[^0-9]([0-9]{2})[^0-9]([0-9]{4})$#', $str_date, $mth)) return "{$mth[3]}-{$mth[2]}-{$mth[1]}";
		return false;
	}
}

//header('Content-type: text/html');
?>