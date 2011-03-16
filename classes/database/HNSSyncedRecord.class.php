<?php

/**
 * HnsApiError is thrown whenever there is an invalid call to or response from
 * the HNS XML API
 */
class HnsApiError extends Exception {
	protected $query = '';

	public function __construct($message, $query = null) {
		$this->message = $message;
		$this->query = $query;
	}

	public function getFullMessage() {
		if (!DEVELOPER || is_null($this->query)) return $this->getMessage();
		return $this->getMessage() .' ("'. htmlentities($this->query) .'")';
	}
}

/**
 * HnsCannotSaveError is (should be) thrown whenever some part of the application
 * attempts to save a Record to HNS, but some precondition prevents the object
 * from being saved. Examples: test or invalid data, Regions (they are read-only)
 */
class HnsCannotSaveError extends Exception {}

/**
 * HnsCannotSyncError is (should be) thrown whenever some part of the application
 * attempts to sync a Record to HNS, but some precondition prpevents the object
 * from being linked to a HNS object. Example: Raadsstukken with show = false
 * are not synced to HNS, so related objects cannot be synced either.
 */
class HnsCannotSyncError extends Exception {}

/**
 * HNSSyncedRecord is used to sync any changes in WSMR to the HNS Dev project.
 */
class HNSSyncedRecord extends Record {
	const RETURN_XML    = 'xml';    // Raw XML string
	const RETURN_XMLDOM = 'xmldom'; // SimpleXml DOM tree
	const RETURN_ARRAY  = 'array';  // DOM tree converted to array

	static protected $queryCount = 0;

	// Multidimensional array: $hnsIds[RecordType][RecordId] = HNSID
	static protected $hnsIds = array();

	public function __construct($id = null) {
		parent::__construct($id);

		if (!isset(self::$hnsIds[$this->className()])) {
			self::$hnsIds[$this->className()] = array();
		}
	}

	/**
	 * For debugging: returns the number of executed HNS queries
	 * @return int   Number of executed HNS queries
	 */
	public function queryCount() {
		return self::$queryCount;
	}

	protected function className() {
		return get_class($this);
	}

	/**
	 * Returns the locally stored HNS ID of the current Record object
	 * @return    int      HNS ID
	 * @ return   bool     false
	 */
	public function hnsId() {
		if (isset(self::$hnsIds[$this->className()][$this->id])) {
			return self::$hnsIds[$this->className()][$this->id];
		}

		self::$hnsIds[$this->className()][$this->id] = false;

		if (!$this->id) return false;

		$id = (int) $this->id;

		$sql = "SELECT hns_id FROM sys_hns_ids WHERE record_type = '{$this->className()}' AND record_id = {$id}";

		$row = $this->db->query($sql)->fetchRow();

		if (!$row) return false;

		$this->debug("{$this->className()}({$id}) already linked to HNS ID: {$row['hns_id']}", 'debug');

		self::$hnsIds[$this->className()][$this->id] = $row['hns_id'];

		return self::$hnsIds[$this->className()][$this->id];
	}

	public function hasHnsId() {
		return (bool) $this->hnsId();
	}

	/**
	 * Saves a HNS ID for the current Record in the local database
	 * @param int $hnsId
	 */
	protected function saveHnsId($hnsId) {
		$id = $this->id;

		$sql = "INSERT INTO sys_hns_ids (record_type, record_id, hns_id) VALUES ('{$this->className()}', {$id}, {$hnsId})";

		$this->debug("{$this->className()}({$this->id}) saving HNS ID: {$hnsId}", 'debug');

		$this->db->query($sql);

		self::$hnsIds[$this->className()][$this->id] = $hnsId;
	}

	public function save() {
		// Store in temp variable, because $this->dirty is changed by parent::save()
		$do_sync = $this->shouldSyncToHns();

		$value = parent::save(); //First save local. Don't sync if parent fails.

		if ($do_sync && !HNS_DISABLE_SYNC) $this->syncToHns();

		return $value;
	}

	protected function shouldSyncToHns() {
		if (HNS_DISABLE_SYNC)   return false;
		if ($this->dirty)       return true;
		if (!$this->hasHnsId()) return true;

		return false;
	}

	/**
	 * 
	 * @return     bool       true
	 * @throws     HnsCannotSyncError If Record cannot be synced to HNS for some reason
	 *
	 * Example: Regions can be synced to HNSdev, but they cannot be saved.
	 */
	 public function verifyCanSyncToHns() {
		return true;
	}

	/**
	 *
	 * @return     bool       true
	 * @throws     HnsCannotSaveError If Record cannot be saved to HNS for some reason
	 *
	 * Example: Regions can be synced to HNSdev, but they cannot be saved.
	 */
	public function verifyCanSaveInHns() {
		return true;
	}

	final protected function syncToHns() {
		try {
			$this->verifyCanSyncToHns();

			if ($this->hasHnsId() && $this->verifyCanSaveInHns()) {
				$this->updateHnsEntry();

				return;
			}

			// Try to retrieve HNS id from matching HNS object(s)
			$hnsId = $this->fetchHnsId();

			// If there is no matching object in the HNS DB, create it
			if (empty($hnsId) && $this->verifyCanSaveInHns()) {
				$hnsId = $this->insertHnsEntry();
			}

			if ($this->hnsObjectExistsInDb($hnsId)) {
				throw new HnsApiError("{$this->className()}, {$hnsId} already exists. Do you need to TRUNCATE sys_hns_ids?");
			}

			// Current object does not hasHnsId(), so save $hnsId in the local DB
			$this->saveHnsId($hnsId);
		} catch(Exception $ex){
            // HNS_SYNCING: pages/watstemtmijnraad/hnsdev/php/indexPage.class.php
            // HNS_SYNCING: suppress (dozens of!) Exceptions during sync
			if(DEVELOPER || (defined('HNS_SYNCING') && HNS_SYNCING == true)){
				throw $ex;
			} elseif (is_a($ex, 'HnsCannotSyncError')) {
                // Ignore
            } elseif (is_a($ex, 'HnsCannotSaveError')) {
                // Ignore
            } else {
				//Mail exception to exceptions@getlogic.nl, but do not show to the user.
				$this->mailExceptionToDeveloper($ex);
			}
		}

		return true;
	}

	protected function getHnsMapping() {
		$mapping = array(
			'hnsTable' => '',
			'fields' => array(
				'WSMR_field' => 'HNS_field'
			)
		);

		throw new Exception('getHnsMapping needs to be subclassed by '. get_class($this));
	}

	protected function hnsTable() {
		$mapping = $this->getHnsMapping();

		return $mapping['hnsTable'];
	}

	/**
	 * Returns the list of fields and their names in HNS that need to be saved
	 * in the remote database
	 */
	protected function hnsFields() {
		$mapping = $this->getHnsMapping();

		return $mapping['fields'];
	}

	/**
	 * @return    null       If no matching object is found
	 * @return    int        HNS ID
	 */
	protected function fetchHnsId() {
		if ($this->hasHnsId()) return $this->hnsId();

		$result = $this->retrieveMatchingHnsObjects();

		if (empty($result[$this->hnsTable()])) return null;

		$hnsObjects = $result[$this->hnsTable()];
		$bestMatch  = $this->bestMatchFromHnsObjects($hnsObjects);

		if (!isset($bestMatch['id'])) return false;

		$this->debug("{$this->className()}({$this->id}) matches HNS ID: {$bestMatch['id']}", 'success');

		return $bestMatch['id'];
	}

	/**
	 * @return array     Matching objects (zero or more)
	 */
	protected function retrieveMatchingHnsObjects(){
		$query  = $this->buildMatchQuery();
		$result = $this->execute($query);

		if (empty($result[$this->hnsTable()])) return array();

		return $result;
	}

	protected function buildMatchQuery() {
		$fields  = $this->getHnsUniqueCheck();
		$where   = array();

		foreach ($fields as $localKey => $hnsKey) {
			$value = $this->$localKey;

			if (strlen($value) == 0 && !is_bool($value)) continue;

			$where[] = $hnsKey .' = \''. $this->sanitizeValue($value) .'\'';
		}

		$query = $this->compileSelectQuery(array(), $where);

		return $query;
	}

	/**
	 * $this->uniques format: array(
	 * 'localMember' -> 'hnsAttr'
	 * 'hns_parent_id' -> 'parent'
	 * 'hns_region_id' -> 'area.id')
	 */
	protected $uniques = false;

	protected function getHnsUniqueCheck() {
		if (!is_array($this->uniques)) {
			throw new Exception(get_class($this) .'->uniques is undefined (needs to be lookup hash)');
		}

		return $this->uniques;
	}

	protected function bestMatchFromHnsObjects($hnsObjects) {
		foreach ($hnsObjects as $object) {
			if ($this->hnsObjectExistsInDb($object['id'])) {
				$this->debug("Record {$this->className()}({$this->id}) already has HNS ID: {$object['id']}");

				continue;
			}

			return $object;
		}

		return array();
	}

	protected function hnsObjectExistsInDb($hnsId) {
		$sql = "SELECT * FROM sys_hns_ids WHERE record_type = '{$this->className()}' AND hns_id = {$hnsId}";

		$row = $this->db->query($sql)->fetchRow();

		if (!$row) return false;

		return true;
	}

	protected function updateHnsEntry($query = null) {
		if (is_null($query)) {
			$query  = $this->_compileInsertOrUpdateQuery('update');
		}

		$result = $this->execute($query);

		$this->debug("{$this->className()}($this->id) updated in HNS", 'success');

		return;
	}

	protected function insertHnsEntry($query = null, $createdType = null) {
		$query  = is_null($query) ? $this->_compileInsertOrUpdateQuery('insert') : $query;

		$result = $this->execute($query);

		$hnsId  = $this->extractHnsId($result, $createdType);

		$this->debug("{$this->className()}($this->id) inserted into HNS (ID: {$hnsId})", 'success');

		return $hnsId;
	}

	protected function extractHnsId($result, $createdType = null) {
		$createdType = is_null($createdType) ? $this->hnsTable() : $createdType;

		if (!isset($result[$createdType][0]['id'])) {
			throw new HnsApiError('Insert query did not return a valid ID', $query);
		}

		$hnsId  = $result[$createdType][0]['id'];

		return $hnsId;
	}

	protected $extraFields = array();

	/**
	 * Creates the needed query
	 * @param $type String Type of the query to be created
	 * @return String Created query
	 */
	private function _compileInsertOrUpdateQuery($type) {
		if($type == 'update'){
			$query   = '<'.$type.'><'.$this->hnsTable().' id="'.$this->hnsId().'">';
		} else {
			$query   = '<'.$type.'><'.$this->hnsTable().'>';
		}

		$fields = $this->hnsFields();

		foreach ($fields as $localKey => $hnsKey) {
			$value = $this->$localKey;

			if (is_null($value)) continue;
			if (strlen($value) == 0 && !is_bool($value)) continue;

			$query .= '<'.$hnsKey.'>'. $this->sanitizeValue((string) $value) .'</'.$hnsKey.'>';
		}

		foreach ($this->extraFields as $fieldName => $callbackFunction) {
			$query .= call_user_func(array($this, $callbackFunction));
		}

		$query .= '</'.$this->hnsTable().'></'.$type.'>';

		return $query;
	}

	/**
	 * Creates a select query.
	 * @param $columns Array with WSMR column names that need to be selected
	 * @param $where Array with logic that will be in the <where>-tags
	 * @return String Created query
	 */
	final protected function compileSelectQuery($columns = array(), $where = array()) {
		$hnsColumns = $columns;
		$mapping    = $this->getHnsMapping();

		//Get the HNS columns we want to select
		if(empty($columns)) {
			foreach ($this->hnsFields() as $col => $hnsCol) {
				$hnsColumns[] = $hnsCol;
			}
		}

		//Build the query
		$query = '<query>';
		foreach($hnsColumns as $column){
			$query .= '<select>'.$column.'</select>';
		}
		$query .= '<from>'.$this->hnsTable().'</from>';
		foreach ($where as $condition) {
			$query .= '<where>'.$condition.'</where>';
		}
		$query .= '</query>';

		return $query;
	}

	/**
	 * Executes the given query. Got this code from http://dev.hetnieuwestemmen.nl
	 * @param $query String XML query
	 * @return String XML result
	 */
	final protected function execute($query, $return_type = self::RETURN_ARRAY) {
		//Initialize
		$url      = HNSDEV_URL;
		$username = HNSDEV_USER;
		$privKey  = HNSDEV_KEY;

		if (DEBUG_HNSDEV_QUERIES) {
			$this->debug("Executing query: ". $query, 'debug');
		}

		$priv       = openssl_get_privatekey($privKey);
		openssl_sign($query, $privKey, $priv);
		openssl_free_key($priv);
		$key        = reset(unpack('H*', $privKey));

		$url .= '?user='.urlencode($username).'&key='.urlencode($key);

		//Connect to HNS-Dev and execute query
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
		$xmlOut = curl_exec($ch);

		self::$queryCount++;

		$errorNr    = curl_errno($ch);
		$errorMsg   = curl_error($ch);
		$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		if ($errorNr) {
			throw new HnsApiError("Curl error: {$errorNr} ($errorMsg)", $query);
		}

		if (strlen($xmlOut) == 0) {
			throw new HnsApiError('HNS API returned zero-length XML string', $query);
		}

		$xmlDom = simplexml_load_string($xmlOut);

		if ($xmlDom === false) {
			throw new HnsApiError('Could not parse XML returned by HNS API', $xmlOut);
		}

		if ($httpStatus != 200) {
			if ($xmlDom->code && $xmlDom->code[0] != 200) {
				throw new HnsApiError($xmlDom->code[0] .': '. $xmlDom->message[0], $query);
			}

			throw new HnsApiError('Received HTTP response '. $httpStatus .' with invalid error XML', $query);
		}

		if ($return_type == self::RETURN_XML)    return $xmlOut;
		if ($return_type == self::RETURN_XMLDOM) return $xmlDom;

		return $this->xmlToArray($xmlDom);
	}

	function mailExceptionToDeveloper($e){
		$data = array(
			'message'   => $e->getMessage(),
			'file'      => $e->getFile(),
			'line'      => $e->getLine(),
			'trace'     => $e->getTrace(),
			'data'      => $e instanceof DataException ? $e->getData() : false,
			'exception' => get_class($e),
			'developer' => DEVELOPER
		);

		require_once('HtmlMailer.class.php');
		global $dispatcher;
		$mail = new HtmlMailer(new CustomSmarty($dispatcher->locale));
		$mail->setTemplate($_SERVER['DOCUMENT_ROOT'].'/../emails/'.$dispatcher->activeSite['publicdir'].'/'.$dispatcher->activeSite['template']);
		$mail->setSubject('Exception for '.$dispatcher->activeSite['title']);
		$mail->setContent($_SERVER['DOCUMENT_ROOT'].'/../emails/exception.html');
		$mail->setFrom($dispatcher->activeSite['systemMail'], $dispatcher->activeSite['title']);

		$mail->assignByRef('data', $data);
		$mail->addAddress('exceptions@getlogic.nl');
		$mail->send();
	}

	/**
	 * Got this from php.net. Seems to work.
	 * @param $xml SimpleXML object to parse
	 * @param $arr Optional Array where the XML is parsed into.
	 * @return Array
	 */
	function xmlToArray($xml){
		$arr = array();

		if (!is_object($xml)) {
			$xml = simplexml_load_string($xml);
		}

		foreach ($xml->attributes() as $key => $value) {
			$arr[$key] = (string) $value;
		}

		foreach($xml->children() as $child){
			$childName = $child->getName();
			if(!$child->children()){
				$arr[$childName] = trim($child[0]);
			} else {
				if (!isset($arr[$childName])) $arr[$childName] = array();
				$arr[$childName][] = $this->xmlToArray($child);
			}
		}
		return $arr;
	}

	protected function sanitizeValue($value) {
		$sanitized = escapeForXml(escapeForSql(utf8Entities(strip_tags($value))));

		return $sanitized;
	}

	protected function debug($text, $status = 'default') {
		debug($text, $status);

		if (LOG_HNS_EVENTS) {
			try {
				$this->db->query("INSERT INTO sys_hns_log (class, message) VALUES ('{$status}', %s)", $text);
			} catch (DatabaseQueryException $e) {
				if (DEVELOPER) throw $e;

				$this->mailExceptionToDeveloper($e);
			}
		}
	}
}

function utf8Entities($xml) {
	$current  = $xml;
	$previous = false;

	while ($current != $previous) {
		$previous = $current;
		$current  = html_entity_decode($current, ENT_QUOTES, 'ISO-8859-1');
		$i = (isset($i)) ? 1: ++$i;

		if ($i > 10) throw new Exception('Too many levels of entity-encoding');
	}

	return $current;
}

function escapeForSql($value) {
	return addslashes($value);
}

function escapeForXml($value) {
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function debug($text, $status = 'default') {
	if (DEBUG_HNSDEV_SYNC) {
		$color = isset($colors[$status]) ? $colors[$status] : $colors['default'];

		echo "<div class=\"debug\"><span class=\"{$status}\">", htmlentities($text), "</span></div>\n";
	}
}
