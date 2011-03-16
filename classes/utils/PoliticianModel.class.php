<?php
require_once('TimeRange.class.php');
require_once('RegionModel.class.php');
require_once('PartyModel.class.php');

/**
* Handles politicians.
*
* @author Sardar Yumatov (ja.doma@gmail.com)
*/
class PoliticianModel {

	/** DB table name containing all politicians. */
	const TABLE_NAME = 'pol_politicians';
	/** used by lastInertId() to retrieve the last inserted ID. null for MySQL */
	const ID_SEQUENCE = 'pol_politicians_id_seq';



	protected $id = null;
	protected $def_party = null;

	protected $extid;
	protected $title;
	protected $initials;
	protected $last_name;
	protected $gender;
	protected $email;

	protected $region;
	protected $key;

	/* @var array ("{party}-{region}-{category}" => [start, end, description, category, region, party]) */
	//protected $functions = array();
	
	/** @var AppointmentManager */
	protected $manager;

	protected $schema = null;
	protected $db = null;

	/**
	 * Create new unitialized public politician.
	 *
	 * @param string $initials initials or first name
	 * @param string $last_name last name
	 * @param string $gender 'male' or 'female' gender, since back-end uses boolean here, we require this
	 * @param string $title optional title
	 * @param string $email optional e-mail adres
	 * @param RegionModel $region resolved region object or null if not set
	 * @param string $extid external id optional external (import file wide) id
	 * @param integer $def_party linked party, for internal use only
	 */
	public function __construct($initials, $last_name, $gender, $title = null, $email = null, $region = null, $extid = null, $def_party = null) {
		$this->initials = $initials;
		$this->last_name = $last_name;

		$gender = strtolower(trim($gender));
		if(!in_array($gender, array('male', 'female'))) throw new InvalidArgumentException("Unknown gender: {$gender}, expecting 'male' or 'female'");
		$this->gender = $gender;

		$this->title = $title;
		$this->email = $email;
		$this->extid = $extid;

		if($region !== null && !($region instanceof RegionModel)) throw new InvalidArgumentException("Expecting resolved region! Got: {$region}");
		$this->region = $region;

		$initials = str_replace(array('.', ',', '_', ' '), '', $initials);
		$this->key = self::stem("{$initials}_{$last_name}_{$gender}_{$email}");
		$this->def_party = $def_party;
	}

	/**
	 * Resolve this object.
	 * This method is for internal use only!
	 *
	 * @access package
	 * @param PoliticianModelSchema $schema the parent schema
	 * @param PDO $db database link
	 *
	 */
	public function resolve(PoliticianModelSchema $schema, $db, $id) {
		$this->schema = $schema;
		$this->db = $db;

		if($id === null) {
			if(defined('DRY_RUN')) throw new RuntimeException("Before inserting new politician: ".$this->last_name);

			if ($this->region && defined('REGION_ACCESS') && !isset($GLOBALS['REGION_ACCESS_LIST'][$this->region->getId()]))
				throw new RuntimeException('Not allowed to insert politicians for region: '.$this->region->getName());
			elseif (!$this->region && defined('FULL_ACCESS') && !FULL_ACCESS)
				throw new RuntimeException('Not allowed to insert politicians without region');

			$log = JLogger::getLogger('utils.import.schema.politician');
			$log->preUpdate("Inserting new politician: {$this->last_name}");
			$ins = $this->db->prepare('INSERT INTO '.self::TABLE_NAME.'(title, first_name, last_name, gender_is_male, photo, email, name_sortkey, region_created, def_party) VALUES (:title, :first_name, :last_name, :gender_is_male, :photo, :email, :name_sortkey, :region_created, :def_party);');
			$ins->execute(array(
				':title' => $this->title,
				':first_name' => $this->initials,
				':last_name' => $this->last_name,
				':gender_is_male' => $this->gender == 'male'? 1: 0,
				':photo' => null,
				':email' => $this->email,
				':name_sortkey' => null, //changed by stored procedure
				':region_created' => $this->region? $this->region->getId(): null,
				':def_party' => trim($this->def_party) == ''? null: intval($this->def_party)
			));

            if($ins->rowCount() != 1) {
                ob_start();
                print_r($ins->errorInfo());
                $msg = ob_get_clean();
                $log->error($msg);
                throw new RuntimeException("Can't insert new politician '{$this->last_name}', database failure, rows: !" . $ins->rowCount());
            }

			$this->id = $this->db->lastInsertId(self::ID_SEQUENCE);

			$log->postUpdate("Successfully inserted new politician: {$this->last_name}, id: {$this->id}");
		} else $this->id = $id;

		//[FIXME: O(n) fetches]
		$this->manager = new AppointmentModelManager($this, $db);
	}

	/** Load appointments from duplicate. */
	public function mergeAppointments($id) {
		$this->manager->mergeAppointments($id);
	}
	
	/**
	 * Returns true if this politician is an 'Unknown' or default for a party object.
	 * Default politician is needed to vote from the whole party perspective.
	 * @return boolean true - politician is of a default type
	 */
	public function isDefault() {
		return $this->def_party !== null;
	}


	/**
	 * Returns record ID of this party.
	 * @return integer
	 */
	public function getId() {
		if($this->id === null) throw new RuntimeException("Politician '{$this->last_name}' is not yet resolved!");
		return $this->id;
	}

	/**
	 * Returns <tt>PoliticianModelSchema</tt> that handles all the politicians.
	 * @return PoliticianModelSchema
	 */
	public function getSchema() {
		if($this->schema == null) throw new RuntimeException("Politician '{$this->last_name}' is not yet resolved");
		return $this->schema;
	}

	/** Returns some of read-only properties */
	public function __get($name) {
		switch ($name) {
			case 'title': return $this->title;
			case 'externalId': return $this->extid;
			case 'initials': return $this->initials;
			case 'last_name': return $this->last_name;
			case 'gender': return $this->gender;
			case 'email': return $this->email;
			case 'region': return $this->region;
			
			case 'manager': return $this->manager;

			default:
				throw new InvalidArgumentException("Unknown property: {$name}");
		}
	}


	/**
	 * Returns stemmed concatenation of (region, initials, last_name, gender, email)
	 * @return string
	 */
	public function getKey() {
		return $this->key;
	}


	/**
	 * Returns region where this politician belongs to.
	 * Region together with email is used to distinguish politicians with equal names.
	 *
	 * @return RegionModel the politician region
	 */
	public function getRegion() {
		return $this->region;
	}

	/**
	 * Ensure politician is registered in the $region for specific $party at the [$date_start - $date_end] time range.
	 *
	 * Each politician has one or more functions associated with different categories by different parties in different
	 * time ranges. The politician may vote if and only if he/shi has valid function in associated region.
	 *
	 * Warning: giving $date_start = null, $date_end = null you will register the politician
	 * in the region for unlimited time. Old registrations will not be deleted for archiving reasons.
	 *
	 * @throws RuntimeException on any error
	 * @param RegionModel $region associated region
	 * @param PartyModel $party associated party
	 * @param string $date_start first day as 'YYYY-mm-dd' string, null for 'infinity'
	 * @param string $date_end last day as 'YYYY-mm-dd' string, null for 'infinity'
	 * @param CategoryModel $category optional associated category, if not set, then default "Geen" is used
	 * @param string $description optional function description
	 * @return void
	 */
	public function registerFunction(RegionModel $region, PartyModel $party, $date_start = null, $date_end = null, $category = null, $description = null) {
		if($this->schema == null) throw new RuntimeException("Politician '{$this->last_name}' is not yet resolved");

		if (defined('REGION_ACCESS') && !isset($GLOBALS['REGION_ACCESS_LIST'][$region->getId()]))
			throw new RuntimeException('Not allowed to update politicians for region: '.$region->getName());

		$log = JLogger::getLogger("utils.import.schema.politician");
		if($log->isEnabled(JLogger::ENTER)) $log->enter("Ensuring politician '{$this->last_name}' has valid function in region: {$region->getPath()} by party '{$party->getName()}' for time range: [{$date_start} - {$date_end}]");

		if(($start = ModelSchema::normalizeDate($date_start)) === false) throw new InvalidArgumentException("Incorrect start date: '{$date_start}'. Expecting: yyyy-mm-dd");
		else $date_start = $start;
		
		if(($end = ModelSchema::normalizeDate($date_end)) === false) throw new InvalidArgumentException("Incorrect end date: '{$date_end}'. Expecting: yyyy-mm-dd");
		else $date_end = $end;

		//ensure our party is registered too
		//$party->ensureRegisteredInRegion($region, $date_start, $date_end);

		if($category == null) $category = CategoryModelSchema::NO_CATEGORY_OBJECT;
		else $category = $category->getId();
		
		$stop_party_id = $this->manager->addAppointment($party->getId(), $category, $region->getId(), $date_start == ''? null: $date_start, $date_end == ''? null: $date_end);
		if($stop_party_id != null) throw new RuntimeException("Can't add appointment [{$date_start} - {$date_end}] by party {$party->getId()} ({$party->getName()}) for politician {$this->getId()} ({$this->extid} - {$this->last_name}), it overlaps with appointment of party {$stop_party_id} in the region {$region->getPath()}");
		
		//if($log->isEnabled(JLogger::DEBUG)) $log->debug("Registering appointment for politician {$this->getId()} ({$this->last_name}) in region {$region->getName()} and party {$party->getId()} for range: [{$date_start} - {$date_end}]");
		if($stop_party_id == null && $this->manager->hasChanges()) { //this will stop on wrong input in the file, but will not fix broken import...
			if(defined('DRY_RUN')) throw new RuntimeException("Before registering appointment for politician {$this->getId()} ({$this->last_name}) in region {$region->getId()} ({$region->getName()}) and party {$party->getId()} for range: [{$date_start} - {$date_end}] and that caused changes.");
			if($log->isEnabled(JLogger::PRE_UPDATE)) $log->preUpdate("Before registering appointment for politician {$this->getId()} ({$this->last_name}) in region {$region->getName()} and party {$party->getId()} for range: [{$date_start} - {$date_end}]");
			$this->manager->playChanges($description);
			if($log->isEnabled(JLogger::POST_UPDATE)) $log->postUpdate("Changes played successfully. Registered appointment for politician {$this->getId()} ({$this->last_name}) in region {$region->getName()} and party {$party->getId()} for range: [{$date_start} - {$date_end}]");
		}

		if($log->isEnabled(JLogger::LEAVE)) $log->enter("Leaving registration for politician '{$this->last_name}' in region: {$region->getPath()} by party '{$party->getName()}' for time range: [{$date_start} - {$date_end}]");
	}


	/**
	 * Check if this politician has a valid appointment for any party at given $date.
	 * If this method returns true for Raadsstuk vote_date, then politician can both,
	 * submit the raadsstuk and vote for it.
	 *
	 * @param Region $region associated region
	 * @param string $date date as 'yyyy-mm-dd' string
	 * @return boolean true - politician has appointment at given date, false otherwise
	 */
	public function hasAppointment(RegionModel $region, $date) {
		if($this->schema == null) throw new RuntimeException("Politician '{$this->last_name}' is not yet resolved");

		if(!($sdate = ModelSchema::normalizeDate($date))) throw new InvalidArgumentException("Incorrect date: '{$date}'. Expecting: yyyy-mm-dd");
		else $date = $sdate;

		return $this->manager->hasAppointment($region->getId(), $date);
	}

	/**
	 * Stem the given key.
	 *
	 * @param string $key key to stem
	 * @return string stemmed string
	 */
	public static function stem($key) {
		if(defined('DISABLE_STEM_PARTY')) return ModelSchema::plainNormalize($key);
		return ModelSchema::normalize($key);
	}


	/**
	 * Serialize this politician to the DOM tree.
	 *
	 * @param DOMDocument $dom the owner document, used to create elements
	 * @param DOMElement $root where to 'party' element will be added
	 * @param array $options extra options
	 * @return void
	 */
	public function toXml($dom, $root, $options) {
		$el = $dom->createElement('politician');

		$el->setAttribute('id', $options['politician.external_id']);
		$el->setAttribute('last_name', $this->last_name);
		$el->setAttribute('gender', $this->gender);
		$el->setAttribute('title', $this->title);
		$el->setAttribute('initials', $this->initials);
		$el->setAttribute('email', $this->email);
		if($this->getRegion() != null) $el->setAttribute('region', $this->getRegion()->getPath());

		$root->appendChild($el);

		//$apps = $this->manager->listAppointments();
		$apps = $this->manager->listMergedAppointments();
		$catschema = $this->schema->getGlobalSchema()->getCategorySchema();
		$regschema = $this->schema->getGlobalSchema()->getRegionSchema();
		$partyschema = $this->schema->getGlobalSchema()->getPartySchema();
		
		foreach ($apps as $bla) {
			$r = $dom->createElement('appointment');
			$r->setAttribute('category', $catschema->lookup($bla['category'])->getName());
			$r->setAttribute('region', $regschema->lookup($bla['region'])->getPath());
			$r->setAttribute('party', $partyschema->lookup($bla['party'])->getName());
				
			$r->setAttribute('description', $bla['description']);
			//list($start, $end) = TimeRange::postgresTimes($bla->time_start, $bla->time_end);
			if($bla['start'] != '') $r->setAttribute('date_start', $bla['start']);
			if($bla['end'] != '') $r->setAttribute('date_end', $bla['end']);

			$el->appendChild($r);
		}
	}
	
	/**
	 * Serialize this politician to the XML stream.
	 *
	 * @param XMLWriter $xw XML output stream
	 * @param array $options extra options
	 * @return void
	 */
	public function toXmlWrite($xw, $options) {
		if(!$this->schema->isTraced($this->id)) return;
		
		$xw->startElement('politician'); // <politician>

		$xw->writeAttribute('id', $options['politician.external_id']);
		$xw->writeAttribute('last_name', $this->last_name);
		$xw->writeAttribute('gender', $this->gender);
		$xw->writeAttribute('title', $this->title);
		$xw->writeAttribute('initials', $this->initials);
		$xw->writeAttribute('email', $this->email);
		if($this->getRegion() != null) $xw->writeAttribute('region', $this->getRegion()->getPath());

		$apps = $this->manager->listMergedAppointments();
		$catschema = $this->schema->getGlobalSchema()->getCategorySchema();
		$regschema = $this->schema->getGlobalSchema()->getRegionSchema();
		$partyschema = $this->schema->getGlobalSchema()->getPartySchema();
		
		foreach ($apps as $bla) {
			$xw->startElement('appointment'); // <appointment>
			
			$xw->writeAttribute('category', $catschema->lookup($bla['category'])->getName());
			$xw->writeAttribute('region', $regschema->lookup($bla['region'])->getPath());
			$xw->writeAttribute('party', $partyschema->lookup($bla['party'])->getName());
			$xw->writeAttribute('description', $bla['description']);
			if($bla['start'] != '') $xw->writeAttribute('date_start', $bla['start']);
			if($bla['end'] != '') $xw->writeAttribute('date_end', $bla['end']);

			$xw->endElement(); // </appointment>
		}
		
		if(!$apps) {
			$xw->writeComment("WARNING: I can't find valid appointment for this politician, that means he/she may not vote nor submit raadssukken! In other words you are exporting inconsistent database!");
		}
		
		$xw->endElement(); // </politician>
	}
	
	
	public function touch() {
		$this->schema->trace($this->id);
		if($this->region)
			$this->region->touch();
	}
	
	public function touchAppointment(RegionModel $reg, $votedate) {
		if($this->schema->isTracing()) $this->manager->touchAppointment($reg->getId(), $votedate);
	}
}




/**
* Manages appointments without using Record (completely within PDO).
* 
* [FIXME: copy of Appointment/AppointmentManager]
*/
class AppointmentModelManager {
	
	public $ranges = array();
	private $descriptions = array();
	private $pdo;
	private $model;
	
	private $touched = array();
	
	/**
	 * Construct new manager.
	 * 
	 * @param PoliticianModel associated model (politician)
	 * @param PDO $pdo DB connection
	 */
	public function __construct(PoliticianModel $pol, $pdo) {
		$this->pdo = $pdo;
		$this->model = $pol;
		
		$this->mergeAppointments($pol->getId());
	}
	
	/** Merge appointments from dublicates. */
	public function mergeAppointments($id) {
		$sel = $this->pdo->prepare('SELECT * FROM pol_politician_functions WHERE politician = :polid');
		if(!$sel->execute(array(':polid' => $id))) throw new RuntimeException('Database exception while fetching politician functions');
		$apps = $sel->fetchAll();
		
		//ensure (region, party) unique, exception on category
		foreach ($apps as $func) {
			list($start, $end) = TimeRange::postgresTimes($func['time_start'], $func['time_end']);
			if(!isset($this->ranges[$func['region']])) $this->ranges[$func['region']] = array();
			if(!isset($this->ranges[$func['region']][$func['party']])) $this->ranges[$func['region']][$func['party']] = array();
			
			foreach ($this->ranges[$func['region']] as $party_id => $cats) { //sanity check
				if($party_id != $func['party']) { //may not overlap
					foreach ($cats as $category_range) {
						if($category_range->isOverlaped($start, $end)) {
							throw new RuntimeException("Database is inconsistent. Politician {$id} works for distinct parties {$party_id} and {$func['party']} in the same region {$func['region']} at the same time. Failed appointment {$func['id']} on range [{$start} - {$end}]");
						}
					}
				}
			}
			
			if(!isset($this->ranges[$func['region']][$func['party']][$func['category']])) $this->ranges[$func['region']][$func['party']][$func['category']] = new TimeRange();			
			$this->ranges[$func['region']][$func['party']][$func['category']]->addRange($func['id'], $start, $end);
			$this->descriptions[$func['id']] = $func['description'];
		}
	}
	
	/** Export only consistent data (used by schema export repair) */
	public function listMergedAppointments() {
		$ret = array();
		
		foreach ($this->ranges as $region => $parties) {
			foreach ($parties as $party_id => $categories) {
				foreach ($categories as $category_id => $range) {
					$periods = $range->listRanges(); // [(id, start, end)]
					if($this->model->getSchema()->isTracing() && !isset($this->touched[$region])) continue;
					
					foreach ($periods as $per) {
						$per['region'] = $region;
						$per['party'] = $party_id;
						$per['category'] = $category_id;
						$per['description'] = @$this->descriptions[$per['id']];
						$ret[] = $per;
					}
				}
			}
		}
		
		return $ret;
	}
	
	/**
	 * Add new appointment.
	 * Method returns conflicting party if this politician already has a function in this region for another party.
	 * 
	 * @return PartyModel the conflicting party or null if everything is OK
	 */
	public function addAppointment($target_party, $target_category, $region, $start, $end) {
		if(!isset($this->ranges[$region])) $this->ranges[$region] = array();
		foreach ($this->ranges[$region] as $party_id => $categories) {
			if($target_party != $party_id) { //invariant: at any time only one (party, region)
				foreach ($categories as $rng) {
					if($rng->isOverlaped($start, $end)) return $party_id;
				}
			}
		}
		
		if(!isset($this->ranges[$region][$target_party])) $this->ranges[$region][$target_party] = array();
		if(!isset($this->ranges[$region][$target_party][$target_category])) $this->ranges[$region][$target_party][$target_category] = new TimeRange();
		
		$this->ranges[$region][$target_party][$target_category]->addRange(null, $start, $end);
		return null;
	}
	
	public function touchAppointment($region, $date) {
		$this->touched[$region] = true;
		$polschema = $this->model->getSchema()->getGlobalSchema()->getPartySchema();
		$catschema = $this->model->getSchema()->getGlobalSchema()->getCategorySchema();
		
		foreach ($this->ranges[$region] as $party_id => $categories) {
			$pat = $polschema->lookup($party_id);
			$pat->touch();
			$pat->touchInRegion($region);
			
			foreach ($categories as $cat_id => $rng) {
				$catschema->lookup($cat_id)->touch();
			}
		}
	}
	
	/**
	 * Returns true if this politician has any appointment at given date.
	 *
	 * @param integer $region region id
	 * @param string $date date as 'yyyy-mm-dd'
	 * @return boolean true - there is an appointment, false otherwise
	 */
	public function hasAppointment($region, $date) {
		if(isset($this->ranges[$region])) {
			foreach ($this->ranges[$region] as $pid => $categories) {
				foreach ($categories as $cat => $rng) {
					if($rng->containsDate($date)) return true;
				}
			}
		}
		return false;
	}
	
	
	/**
	 * Returns true if there are pending changes.
	 * @return boolean
	 */
	public function hasChanges() {
		foreach ($this->ranges as $parties) {
			foreach ($parties as $categories) {
				foreach ($categories as $rng) {
					$changes = $rng->playChanges();
					if(!empty($changes)) return true;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Execute changes directly.
	 * Dirty hack supporting /classes/util/{importer}.
	 * The changes must be done within current transaction to ensure referental integrity.
	 *
	 * FIXME: looks like using PDO was a great idea (simplifies a lot of things), but now results in
	 * complications.
	 * 
	 */
	public function playChanges($description = null) {
		$insert = $this->pdo->prepare("INSERT INTO pol_politician_functions(politician, party, region, category, time_start, time_end, description)
		                         VALUES(:politician, :party, :region, :category, :time_start, :time_end, :description)");
		
		$update = $this->pdo->prepare("UPDATE pol_politician_functions SET
										politician = :politician,
										party = :party,
										region = :region,
										category = :category,
										time_start = :time_start,
										time_end = :time_end,
										description = :description
								 WHERE id = :id");
		
		$delete = $this->pdo->prepare("DELETE FROM pol_politician_functions WHERE id = :id");
  
		$changed = false;
		$patch = array();
		
		$partyschema = $this->model->getSchema()->getGlobalSchema()->getPartySchema();
		$regionschema = $this->model->getSchema()->getGlobalSchema()->getRegionSchema();
		foreach ($this->ranges as $region => $parties) {
			foreach ($parties as $party_id => $categories) {
				foreach ($categories as $category => $rng) {
					$changes = $rng->playChanges();

					foreach ($changes as $chg) {
						$changed = true;

						if($chg['action'] == 'delete') {
							if($chg['id'] < 0) throw new RuntimeException("Trying to delete non existing appointment: {$chg['id']}. Time-range id handling bug detected.");
							$delete->execute(array(':id' => $chg['id']));
							if($delete->rowCount() != 1) throw new RuntimeException("Can't delete politician ({$this->model->getId()}) appointment ({$chg['id']}), database error!");
						} else {
							$data = array(
								':politician' => $this->model->getId(),
								':party' => $party_id,
								':region' => $region,
								':category' => $category,
								':time_start' => $chg['start']? $chg['start']: '-infinity',
								':time_end' => $chg['end']? $chg['end']: 'infinity',
								':description' => $description
							);
							
							if($chg['action'] == 'update') {
								if($chg['id'] < 0) throw new RuntimeException("Trying to update non existing appointment: {$chg['id']}. Time-range id handling bug detected.");
								$data[':id'] = $chg['id'];
								$update->execute($data);
								if($update->rowCount() != 1) throw new RuntimeException("Can't update politician ({$this->model->getId()}) appointment ({$chg['id']}) [{$chg['start']} - {$chg['end']}], database error!");
							} else {
								$insert->execute($data);
								if($insert->rowCount() != 1) throw new RuntimeException("Can't insert politician ({$this->model->getId()}) appointment ({$chg['id']}) [{$chg['start']} - {$chg['end']}], database error!");
								$patch[$chg['id']] = $this->pdo->lastInsertId('pol_politician_functions_id_seq');
							}
							
							//ensure party registrations stay correct
							$partyschema->lookup($party_id)->ensureRegisteredInRegion($regionschema->lookup($region), $chg['start'], $chg['end']);
						}
					}
				
					$rng->clearChanges($patch);
				}
			}
		}

		return $changed;
	}
}

?>
