<?php

//require_once('Answer.class.php');

require_once('Party.class.php');
require_once('Region.class.php');
require_once('LocalParty.class.php');
require_once 'HNSSyncedRecord.class.php';

/**
* Politician functions.
*/
class Appointment extends HNSSyncedRecord {
	protected $data = array(
		'politician'  => null,
        'politician_first_name' => null,
        'politician_last_name' => null,
		'party'       => null,
		'party_name'  => null,
		'region'      => null,
		'region_name' => null,
		'level'       => null,
		'level_name'  => null,
		'category'    => null,
		'cat_name'    => null,
		'time_start'  => null,
		'time_end'    => null,
		'description' => null
	);
	protected $extraCols = array(
        'politician_first_name' => 'pol.first_name',
        'politician_last_name' => 'pol.last_name',
		'party_name'  => 'p.name',
		'region_name' => 'r.name',
		'cat_name'    => 'c.name',
		'level'       => 'l.id',
		'level_name'  => 'l.name'
	);
	protected $multiTables = '
		pol_politician_functions t
        JOIN pol_politicians pol ON pol.id = t.politician
 		JOIN pol_parties p ON p.id = t.party
		JOIN sys_regions r ON r.id = t.region
		JOIN sys_categories c ON c.id = t.category
		JOIN sys_levels l on r.level = l.id';

	protected $tableName = 'pol_politician_functions';

	/** Fetched associates */
	protected $party_obj = null;
	protected $region_obj = null;
	protected $poli_obj = null;
	protected $localparty_obj = null;


	public function loadByPoliticianAndRegion($politician, $region, $order='', $limit=''){
		return $this->getList('', $this->db->formatQuery('WHERE t.politician = %i AND t.region = %i', $politician, $region), $order, $limit);
	}

	public function loadByPolitician($politician, $order='', $limit='') {
		return $this->getList('', $this->db->formatQuery('WHERE t.politician = %i', $politician), $order, $limit);
	}

	public function loadActiveByPolitician($politician, $order='', $limit='') {
		if(is_array($politician)) {
			if(count($politician) < 1) return null;
			$q = 'IN ('.implode(', ', array_map('intval', $politician)).')';
		} else $q = ' = '.intval(_id($politician));
		
		return $this->getList('', "WHERE t.politician {$q} AND time_end > now()", $order, $limit);
	}

    public function countByRegion($region_ids) {
        if(!is_array($region_ids))
            return;

        return $this->db->query('SELECT region, COUNT(*) FROM ' . $this->tableName . ' WHERE region IN (' . implode(', ', $region_ids) . ') GROUP BY region')->fetchAllRows();
    }
    
	/**
	 * Returns associated party.
	 * @return Party associated party or null if this record is not loaded
	 */
	public function getParty() {
		if($this->party_obj == null && $this->party !== null) {
			$this->party_obj = new Party();
			$this->party_obj->load($this->party);
		}
		return $this->party_obj;
	}

	/**
	 * Returns associated region.
	 * @return Region associated region or null if this record is not loaded
	 */
	public function getRegion() {
		if($this->region_obj == null && $this->region !== null) {
			$this->region_obj = new Region();
			$this->region_obj->load($this->region);
		}
		return $this->region_obj;
	}

	public function getPolitician() {
		if($this->poli_obj == null && $this->politician !== null) {
			$this->poli_obj = new Politician();
			$this->poli_obj->load($this->politician);
		}

		return $this->poli_obj;
	}

	public function getLocalParty() {
		if (is_null($this->party)) {
			throw new Exception('Appointment party should not be null');
		}

		if (is_null($this->region)) {
			throw new Exception('Appointment region should not be null');
		}

		if($this->localparty_obj == null ) {
			$localparty = new LocalParty();
			$this->localparty_obj = $localparty->getByAppointmentId($this->id);
		}

		if (empty($this->localparty_obj)) {
			throw new NoSuchRecordException("Appointment({$this->id}) does not have a valid LocalParty reference");
		}

		return $this->localparty_obj;
	}

	/**
	 * Returns true if this function/appointment is expired.
	 * @return boolean
	 */
	public function isExpired() {
		$cur = date('Y-m-d H:i:s');
		$left = $this->time_start != '-infinity' && $cur < $this->time_start; //before start date
		$right = $this->time_end != 'infinity' && $cur > $this->time_start; //after end date
		return $left || $right;
	}


	/**
	 * Returns list of all appointments associated with given category.
	 *
	 * @param Category|integer $category category association
	 * @return array of Appointment
	 */
	public static function listForCategory($category) {
		$catid = is_object($category)? $category->id: intval($category);
		$lst = new Appointment();
		return $lst->getList('', 'WHERE category = ' . $catid, 'ORDER BY politician');
	}

	/**
	 * List appointments associated with given party.
	 * @param Party|integer $party associated party
	 * @param Region|integer $region associated region, null to ignore
	 * @return array of Appointment
	 */
	public static function listByParty($party, $region = null) {
		$lst = new Appointment();
		return $lst->getList('', 'WHERE party = ' . _id($party) . ($region !== null? ' AND region = '._id($region): ''), 'ORDER BY politician');
	}

    public function countByParty($party, $region = null) {
        return $this->db->query('SELECT COUNT(*) FROM (SELECT COUNT(*) FROM ' . $this->tableName . ' WHERE party = %i AND region = %i GROUP BY politician) as pol_count', $party, $region)->fetchCell();
    }

	///////////////////////////////////////////////////////
	//                                                   //
	//     HNS.dev SYNCHRONISATION RELATED FUNCTIONS     //
	//                                                   //
	///////////////////////////////////////////////////////

	public function __get($key) {
		switch ($key) {
			case 'hns_person_id':
				$politician = $this->getPolitician();
				if (!$politician->hasHnsId()) $politician->save();
				return $politician->hnsId();
			case 'hns_organization_id':
				$localparty = $this->getLocalParty();
				if (!$localparty->hasHnsId()) $localparty->save();
				return $localparty->hnsId();
			case 'hns_organization_name':
				return $this->getLocalParty()->party_name;
			case 'hns_function_id':
				return $this->getHnsFunctionId();
			case 'hns_function_name':
				$description = substr($this->description, 0, 50);
				return empty($description) ? 'Raadslid' : $description;
			case 'hns_start_date':
				$timeStart = $this->time_start;
				if (strlen($timeStart) == 0 || strtolower($timeStart) == '-infinity') return null;

				return preg_replace('/^(\d{4}-\d{2}-\d{2}).*/', '$1', $timeStart);
			case 'hns_end_date':
				$timeEnd = $this->time_end;
				if (strlen($timeEnd) == 0 || strtolower($timeEnd) == 'infinity') return null;

				return preg_replace('/^(\d{4}-\d{2}-\d{2}).*/', '$1', $timeEnd);
			default:
				return parent::__get($key);
		}
	}

	public function verifyCanSyncToHns() {
		try {
			$localparty = $this->getLocalParty();
		}
		catch (NoSuchRecordException $e) {
			throw new HnsCannotSyncError('Appointment does not have a valid LocalParty');
		}

        $politician = $this->getPolitician();
        $politician->verifyCanSyncToHns();

		return parent::verifyCanSyncToHns();
	}

	protected function getHnsFunctionId() {
		$functionName = addslashes($this->hns_function_name);

		$query = "
			<query>
				<select>id</select>
				<from>functie</from>
				<where>name = '{$functionName}'</where>
				<where>type = 'Politician'</where>
			</query>
		";

		$result = $this->execute($query);

		if (isset($result['functie'][0]['id'])) return $result['functie'][0]['id'];

		$query = "
			<insert>
				<functie>
					<name>{$functionName}</name>
					<type>Politician</type>
				</functie>
			</insert>
		";

		return $this->insertHnsEntry($query, 'functie');
	}

	protected function getHnsMapping() {
		$mapping = array(
			'hnsTable'   => 'person_function',
			'fields' => array(
				'hns_person_id'         => 'person',
				'hns_organization_id'   => 'organization',
				'hns_start_date'        => 'start_date',
				'hns_end_date'          => 'end_date',
				'hns_function_id'       => 'function'
			)
		);

		return $mapping;
	}

	protected $uniques = array(
			'hns_organization_id' => 'organization.id',
			'hns_function_name'   => 'function.name',
			'hns_start_date'      => 'start_date',
			'hns_end_date'        => 'end_date'
		);

	protected function getHnsUniqueCheck() {
		// Workaround: prevent HNS from choking on NULL dates
		foreach (array('hns_start_date', 'hns_end_date') as $date) {
			$value = $this->$date;
			if (is_null($value)) {
				unset($this->uniques[array_search($date, $this->uniques)]);
			}
		}

		return $this->uniques;
	}
}



/** Handles changes to appointments. */
class AppointmentManager {

	private $ranges = array();
	private $politician;
	private $appointments;
	private $descriptions = array();


	/**
	 * Construct new manager.
	 *
	 * @param Politician $politician the politician to handle appointments of
	 */
	public function __construct(Politician $politician) {
		require_once('TimeRange.class.php');

		$this->politician = $politician;
		$this->appointments = $this->politician->listAllAppointments();

		//ensure (region, party) unique, exception on category
		foreach ($this->appointments as $fn) {
			list($start, $end) = TimeRange::postgresTimes($fn->time_start, $fn->time_end);
			if(!isset($this->ranges[$fn->region])) $this->ranges[$fn->region] = array();
			if(!isset($this->ranges[$fn->region][$fn->party])) $this->ranges[$fn->region][$fn->party] = array();

			foreach ($this->ranges[$fn->region] as $party_id => $cats) { //sanity check
				if($party_id != $fn->party) { //may not overlap
					foreach ($cats as $category_range) {
						if($category_range->isOverlaped($start, $end)) {
							throw new RuntimeException("Database is inconsistent. Politician {$politician->id} works for distinct parties {$party_id} and {$fn->party} in the same region {$fn->region} at the same time. Failed appointment {$fn->id} on range [{$start} - {$end}]");
						}
					}
				}
			}

			if(!isset($this->ranges[$fn->region][$fn->party][$fn->category])) $this->ranges[$fn->region][$fn->party][$fn->category] = new TimeRange();
			$this->ranges[$fn->region][$fn->party][$fn->category]->addRange($fn->id, $start, $end);
			$this->descriptions[$fn->id] = $fn->description;
		}
	}

	/** List all managed appointments. */
	public function listAppointments() {
		if($this->appointments == null) {
			$this->appointments = $this->politician->listAllAppointments();
		}
		return $this->appointments;
	}


	/** Export only consistent data (used by schema export repair) */
	public function listMergedAppointments() {
		$ret = array();

		foreach ($this->ranges as $region => $parties) {
			foreach ($parties as $party_id => $categories) {
				foreach ($categories as $category_id => $range) {
					$periods = $range->listRanges(); // [(id, start, end)]
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
	 * @param Party|integer $target_party associated party
	 * @param Category|integer $target_category associated category
	 * @param Region|integer $region associated region
	 * @param string $start range start (TimeRange format)
	 * @param string $end range end (TimeRange format)
	 * @return Party the conflictin party or null if everything is OK
	 */
	public function addAppointment($target_party, $target_category, $region, $start, $end) {
		$target_party = _id($target_party);
		$target_category = _id($target_category);
		$region = _id($region);

		if(!isset($this->ranges[$region])) $this->ranges[$region] = array();
		foreach ($this->ranges[$region] as $party_id => $categories) {
			if($target_party != $party_id) { //invariant: at any time only one (party, region)
				foreach ($categories as $category => $rng) {
					if($rng->isOverlaped($start, $end)) {
						$err = new Party();
						$err->load($party_id);
						return $err;
					}
				}
			}
		}

		if(!isset($this->ranges[$region][$target_party])) $this->ranges[$region][$target_party] = array();
		if(!isset($this->ranges[$region][$target_party][$target_category])) $this->ranges[$region][$target_party][$target_category] = new TimeRange();
		$this->ranges[$region][$target_party][$target_category]->addRange(null, $start, $end);
		return null;
	}


	/**
	 * Update range.
	 * Method returns conflicting party if this politician already has a function in this region for another party.
	 *
	 * @param Appointment|integer $appointment appointment to update
	 * @param Category|integer $target_category assign category
	 * @param Region|integer $region associated region
	 * @param string $start range start (TimeRange format)
	 * @param string $end range end (TimeRange format)
	 * @return Party the conflictin party or null if everything is OK
	 */
	public function updateAppointment($appointment, $target_category, $region, $start, $end) {
		$catid = _id($target_category);
		$region = _id($region);
		if(!is_object($appointment)) {
			$ap = new Appointment();
			$ap->load($appointment);
			$appointment = $ap;
		}

		//ensure no conflict
		if(!isset($this->ranges[$region])) $this->ranges[$region] = array();
		foreach ($this->ranges[$region] as $party_id => $categories) {
			if($appointment->party != $party_id) { //invariant: at any time only one (party, region)
				foreach ($categories as $category => $rng) {
					if($rng->isOverlaped($start, $end)) {
						$err = new Party();
						$err->load($party_id);
						return $err;
					}
				}
			}
		}

		//category change
		if($catid != $appointment->category) {
			if(!isset($this->ranges[$region][$appointment->party][$catid])) $this->ranges[$region][$appointment->party][$catid] = new TimeRange();
			$this->ranges[$region][$appointment->party][$catid]->addRange($appointment->id, $start, $end, $this->ranges[$region][$appointment->party][$appointment->category]);
		} else $this->ranges[$region][$appointment->party][$appointment->category]->updateRange($appointment->id, $start, $end);

		return null;
	}


	/**
	 * Returns true if there are pending changes.
	 * @return boolean
	 */
	public function hasChanges() {
		foreach ($this->ranges as $region => $parties) {
			foreach ($parties as $party_id => $categories) {
				foreach ($categories as $category => $rng) {
					$changes = $rng->playChanges();
					if(!empty($changes)) return true;
				}
			}
		}

		return false;
	}


	/**
	 * Commit all changes.
	 * @param string $description the description to set to any changed party
	 * @return boolean true - there were changes, false - no changes
	 */
	public function playChanges($description = null) {
		$locman = array();

		$changed = false;
		$patch = array();

		foreach ($this->ranges as $region => $parties) {
			foreach ($parties as $party_id => $categories) {
				foreach ($categories as $category => $rng) {
					$changes = $rng->playChanges();

					if(!empty($changes) && !isset($locman[$party_id])) $locman[$party_id] = new LocalPartyManager($party_id);
					foreach ($changes as $chg) {
						$changed = true;
						$pr = new Appointment();
						if($chg['id'] > 0) $pr->load($chg['id']);
						if($chg['action'] == 'delete') {
							//no auto shrink party-region registration
							$pr->delete();
						} else {
							$pr->politician = $this->politician->id;
							$pr->party = $party_id;
							$pr->region = $region;
							$pr->category = $category;
							$pr->time_start = $chg['start']? $chg['start']: '-infinity';
							$pr->time_end = $chg['end']? $chg['end']: 'infinity';
							$pr->description = $description;
							$pr->save();

							$patch[$chg['id']] = $pr->id;

							//ensure party registrations stay correct
							//[FIXME: this is slow and the changes will not be shown to the user!]
							$locman[$party_id]->ensureSyncWithAppointment($pr);
						}
					}

					$rng->clearChanges($patch);
				}
			}
		}

		//play changes if any
		foreach ($locman as $loc) $loc->playChanges();

		if($changed) $this->appointments = null;
		return $changed;
	}



	/**
	 * Execute changes directly.
	 * Dirty hack supporting /classes/util/{importer}.
	 * The changes must be done within current transaction to ensure referental integrity.
	 *
	 * FIXME: looks like using PDO was a great idea (simplifies a lot of things), but now results in
	 * complications.
	 *
	 * @param PDO $pdo opened connection with transaction started
	 */
	public function playChangesInForeignPDO($pdo, $description = null) {
		$insert = $pdo->prepare("INSERT INTO pol_politician_functions(politician, party, region, category, time_start, time_end, description)
		                         VALUES(:politician, :party, :region, :category, :time_start, :time_end, :description)");

		$update = $pdo->prepare("UPDATE pol_politician_functions SET
										politician = :politician,
										party = :party,
										region = :region,
										category = :category,
										time_start = :time_start,
										time_end = :time_end,
										description := description
								 WHERE id = :id");

		$delete = $pdo->prepare("DELETE FROM pol_politician_functions WHERE id = :id");

		$locman = array();

		$changed = false;
		$patch = array();

		foreach ($this->ranges as $region => $parties) {
			foreach ($parties as $party_id => $categories) {
				foreach ($categories as $category => $rng) {
					$changes = $rng->playChanges();

					if(!empty($changes) && !isset($locman[$party_id])) $locman[$party_id] = new LocalPartyManager($party_id);
					foreach ($changes as $chg) {
						$changed = true;

						if($chg['action'] == 'delete') {
							if($chg['id'] < 0) throw new RuntimeException("Trying to delete non existing appointment: {$chg['id']}. Time-range id handling bug detected.");
							$delete->execute(array(':id' => $chg['id']));
							if($delete->rowCount() != 1) throw new RuntimeException("Can't delete politician ({$this->politician->id}) appointment ({$chg['id']}), database error!");
						} else {
							$data = array(
								':politician' => $this->politician->id,
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
								if($update->rowCount() != 1) throw new RuntimeException("Can't update politician ({$this->politician->id}) appointment ({$chg['id']}) [{$chg['start']} - {$chg['end']}], database error!");
							} else {
								$insert->execute($data);
								if($insert->rowCount() != 1) throw new RuntimeException("Can't insert politician ({$this->politician->id}) appointment ({$chg['id']}) [{$chg['start']} - {$chg['end']}], database error!");
								$patch[$chg['id']] = $pdo->lastInsertId('pol_politician_functions_id_seq');
							}

							//ensure party registrations stay correct
							$locman[$party_id]->ensureSyncWithRange($region, $chg['start'], $chg['end']);
						}
					}

					$rng->clearChanges($patch);
				}
			}
		}

		//play changes if any
		foreach ($locman as $loc) $loc->playChangesInForeignPDO($pdo);

		if($changed) $this->appointments = null;
		return $changed;
	}
}

?>
