<?php
require_once('ObjectList.class.php');
require_once('Appointment.class.php');
require_once('HNSSyncedRecord.class.php');


class Politician extends HNSSyncedRecord {
	protected $data = array(
		'title'          => null,
		'first_name'     => null,
		'last_name'      => null,
		'gender_is_male' => null,
		'photo'          => null,
		'email'          => null,
		'region_created' => null,
		'extern_id'	     => null
	);
	protected $tableName = 'pol_politicians';
	protected $functionsTableName = 'pol_politician_functions';
	protected $politicianUsersTableName = 'pol_politician_users';
	protected $timeKey = 'time';

	protected static $gettext = null;

	public function loadFiltered($filter, $order = '') {
		$query = 'SELECT DISTINCT t.* FROM '.$this->tableName.' t JOIN '.$this->functionsTableName.' f ON t.id = f.politician WHERE TRUE';
		foreach($filter as $key => $val) {
			if (strtolower($key) == $this->timeKey)
				$query .= $this->db->formatQuery(' AND % BETWEEN f.time_start AND f.time_end', $val);
			else
				$query .= $this->db->formatQuery(' AND f.'.$key.'=%', $val);
		}
		$politicians = $this->db->query($query.' '.$order)->fetchAllRows();
		$politicianList = new ObjectList(get_class());
		foreach ($politicians as $politician) {
			$obj = new Politician();
			$obj->loadFromArray($politician);
			$politicianList->add($obj);
		}
		return $politicianList;
	}

	public function loadByBoUser($boUser, $order='', $limit='') {
		return $this->getList('JOIN '.$this->politicianUsersTableName.' u ON t.id = u.politician', 'WHERE u.bo_user = '.$boUser, $order, $limit);
	}


	/** Load politician together with its appointment info for given local party */
	public function loadByParty($localparty, $includeExpired = false, $order='', $limit='') {
		//[FIXME: funny enough, while appointments are naturaly "contained" in local parties (used as that, designed as that)
		//they are not directly attached to local parties, but by (party, time interval) key. not only stupid, but
		//extremely error prone design]
		if(is_array($localparty)) {
			if(count($localparty) < 1) return array();
			$localparty = 'pr.id IN ('.implode(', ', array_map('intval', $localparty)).')';
		} else $localparty = 'pr.id = '.intval($localparty);

		$query = 'SELECT t.*, pf.id AS pid, pf.party, pf.time_start, pf.time_end, pf.description, c.name AS cat_name, (CASE WHEN now() < pf.time_end THEN 0 ELSE 1 END) as expired FROM '.$this->tableName.' t JOIN '.$this->functionsTableName.' pf ON t.id = pf.politician JOIN pol_party_regions pr ON pf.party = pr.party AND pf.region = pr.region JOIN sys_categories c ON pf.category = c.id WHERE '.(!$includeExpired ? 'pf.time_end > now() AND ' : '').$localparty;
		return $this->db->query($query.' '.$order.' '.$limit)->fetchAllRows();
	}

	public function formatName($showTitle = true) {
		$value = '';
		if($showTitle){
			if($this->title && $this->title != '') {
				$value .= $this->title.' ';
			} elseif($this->gender_is_male) {
				$value .= 'dhr. ';
			} else {
				$value .= 'mevr. ';
			}
		}

		$value .= $this->first_name.' '.$this->last_name;
		return $value;
	}

	public static function staticFormatName($data, $showTitle = true) {
		$value = '';
		if($showTitle){
			if($data['title'] && $data['title'] != '') {
				$value .= $data['title'].' ';
			} elseif($data['gender_is_male']) {
				$value .= 'dhr. ';
			} else {
				$value .= 'mevr. ';
			}
		}

		$value .= $data['first_name'].' '.$data['last_name'];
		return $value;
	}

	public function formatSortName() {
		return Politician::formatPoliticianSortName($this->title, $this->first_name, $this->last_name, $this->gender_is_male);
	}

	protected static function gettext() {
		if (!Politician::$gettext) {
			Politician::$gettext = new GettextPO($_SERVER['DOCUMENT_ROOT'].'/../locale/'.
			(Dispatcher::inst()->locale ? Dispatcher::inst()->locale : 'nl').'/title.po');
		}
		return Politician::$gettext;
	}

	public static function formatPoliticianName($title, $firstName, $lastName, $gender) {
		$genderTitle = ''; //Politician::gettext()->getMsgstr('title.'.($gender ? 'male' : 'female'));
		if ($firstName != null)
			return ($gender ? '' : $genderTitle.' ').$title.' '.$firstName.' '.$lastName;
		else
			return ($gender && isset($title) ? '' : $genderTitle.' ').$title.' '.ucfirst($lastName);
	}

	public static function formatPoliticianSortName($title, $firstName, $lastName, $gender) {
		$namePrefix = '';
		if (preg_match('/^([^A-Z]+)([A-Z].*)$/', $lastName, $matches)) {
			$namePrefix = trim($matches[1]);
			$lastName = trim($matches[2]);
		}

		$genderTitle = ''; //Politician::gettext()->getMsgstr('title.'.($gender ? 'male' : 'female'));
		if ($firstName != null)
			return $lastName.', '.($gender ? '' : $genderTitle.' '). ($title ? $title.' ' : '') .$firstName. ($namePrefix ? ' '.$namePrefix : '');
		else
			return $lastName.' '.($gender && isset($title) ? '' : $genderTitle.' ').$title. ($namePrefix ? ' '.ucfirst($namePrefix) : '');
	}

	public static function getDropDownPoliticiansAll($region = null) {
		$p = new Politician();
        if($region) {
            $ps = $p->getList($join = 'JOIN pol_politician_functions AS ppf ON t.id = ppf.politician', $where = 'WHERE NOW() BETWEEN ppf.time_start AND ppf.time_end AND ppf.region = '.$region, $order = 'ORDER BY name_sortkey, last_name, first_name');
        } else { //for autocompleter on home
            $ps = $p->getList('JOIN pol_politician_functions AS ppf ON t.id = ppf.politician JOIN sys_regions r ON r.id = ppf.region', 'WHERE r.hidden = 0 AND NOW() BETWEEN ppf.time_start AND ppf.time_end', 'ORDER BY name_sortkey, last_name, first_name');
        }

		$result = array();
		foreach($ps as $p) {
			$result[$p->id] = $p->formatSortName();
		}

		return $result;
	}
   public static function getDropDownPoliticiansAllWithoutFunction() {
 		$p = new Politician();
		$ps = $p->getList($join = '', $order = 'ORDER BY name_sortkey, last_name, first_name');

		$result = array();
		foreach($ps as $p) {
			$result[$p->id] = $p->formatSortName();
		}

		return $result;
        }

	/**
	 * Returns Smarty ready list of politicians
	 *
	 * FIXME: nobody knows why $party is the Party id if $region is null,
	 * otherwise the $party is expected to be LocalParty id. Weird logic, but
	 * somehow it works...
	 *
	 * @param Party|integer $party associated party
	 * @param Region|integer $region limit to region
	 * @param boolean $includeExpired include politicians with no valid function in selected region
	 * @return array (id => name)
	 */
	public static function getDropDownPoliticians($party, $region = null, $includeExpired = false) {
		$pol = new Politician();

		//JOIN sys_categories c ON pf.category = c.id
		return array_map(create_function('$p', 'return $p->formatName();'),
				 $pol->getList($pol->db->formatQuery('JOIN pol_politician_functions pf ON t.id = pf.politician
				                JOIN pol_party_regions pr ON pf.party = pr.party AND pf.region = pr.region

				                WHERE '.(!$includeExpired ? 'pf.time_end > now() AND ' : '').'pr.'.($region ? 'party' : 'party').' = %i '.($region ? ' AND pr.region = %i' : '').' ORDER BY t.name_sortkey ASC', _id($party), _id($region))));
	}

    public static function getDropDownPoliticiansWithoutParty($region = null, $includeExpired = false) {
        $pol = new Politician();
        return array_map(create_function('$p', 'return $p->formatName();'),
                $pol->getList($pol->db->formatQuery('JOIN pol_politician_functions pf ON t.id = pf.politician
				                JOIN pol_party_regions pr ON pf.party = pr.party AND pf.region = pr.region
				                WHERE '.(!$includeExpired ? 'pf.time_end > now() AND ' : '').' pr.region = 126 ORDER BY t.name_sortkey ASC', _id($region))));
    }
	public function getCountSubmit() {
		$query = 'SELECT COUNT(raadsstuk) FROM rs_raadsstukken_submitters WHERE politician = %i';
		return $this->db->query($query, $this->id)->fetchCell();
	}

	public function getEmailAddresses() {
		$to = array();
		$cc = array();

		if (isset($this->email)) {
			$to[$this->formatName()] = $this->email;
		}

		$boUser = new BackofficeUser(true);
		$boUsers = $boUser->getList(
			$join = 'JOIN pol_politician_users p ON p.bo_user = t.id',
			$where = 'WHERE p.politician = '.$this->id
		);

		if (count($boUsers) > 0)
			foreach ($boUsers as $user)
				if (count($to) > 0)
					$cc[$user->formatName()] = $user->email;
				else {
					$to[$user->formatName()] = $user->email;
				}

		return array($to, $cc);
	}



//==========================- Cleaned code -===========================

	/**
	 * Returns the most recent function/appointment. Returned appointment can be expired.
	 *
	 * Warning: there is only one (region, politician, time-range) record possible, where time-range's
	 * may not overlap. This function takes all such functions, sorts them by end_time and returns the
	 * first record. If $region is specified, then there is only one non-expired appointment possible.
	 *
	 * WARNING: method doesn't use any explicit caching, each call results in re-fetch.
	 *
	 * @param Region|integer $region restrict to given region
	 * @return Appointment found function or false if such function is not defined
	 */
	public function getLatestAppointment($region = null) {
		return reset($this->listAppointments($region));
	}

	/**
	 * List all function/appointments.
	 * If $region is not specified, then all appointments for each region will be fetched.
	 *
	 * WARNING: method doesn't use any explicit caching, each call results in re-fetch.
	 *
	 * @param Region|integer $region restrict to given region
	 * @return array of Appointment found appointments
	 */
	public function listAppointments($region = null) {
		$rgid = is_object($region)? $region->id: ($region != null? intval($region): null);
		$appointment = new Appointment();
		return $appointment->getList('', $this->db->formatQuery('WHERE t.politician = %i '.($rgid? 'AND t.region = %i ': '').' ORDER BY t.time_end DESC', $this->id, $rgid));
	}

	/**
	 * Returns list of all appointments of this politician.
	 * @return array of Appointment
	 */
	public function listAllAppointments() {
		$appointment = new Appointment();
		return $appointment->getList('', $this->db->formatQuery('WHERE t.politician = %i ORDER BY t.time_end DESC', $this->id));
	}


	/**
	 * Returns function/appointment at specific time.
	 * Method returns appointments with time ranges containing given $time. If
	 * $region is null, then (possibly empty) list of Appointment's will be returned,
	 * otherwise the specified appointment will be returned.
	 *
	 * @param timestamp $time the time (vote_date) as 'yyyy-mm-dd'
	 * @return array|Appointment found function (or list) or false if such function is not defined
	 */
	public function getAppointmentAt($time, $region = null) {
		$rgid = is_object($region)? $region->id: ($region != null? intval($region): null);
		$appointment = new Appointment();
		$rows = $appointment->getList('', $this->db->formatQuery('WHERE t.politician = %i AND %s BETWEEN t.time_start AND t.time_end '.($rgid? 'AND t.region = %i ': '').' ORDER BY t.time_end DESC', $this->id, $time, $rgid));
		return $region == null? $rows: reset($rows);
	}

	/**
	 * Checks if politician can vote for specific raadsstuk by name of given party.
	 * Method returns true iff:
	 *
	 *   - Radsstuk r exists
	 *   - Appointment a exists and:
	 *     -- r.region = a.region - politician works in region of raadsstuk
	 *     -- a.party = $party - politician actually works for given party
	 *     -- r.vote_date between [a.time_start and a.time_end] -- function is not expired at vote_date.
	 *
	 * If any of the conditions above fail, the method will return false.
	 *
	 * @param Party|integer $party associated party
	 * @param Raadsstuk|integer $rad the raadsstuk to vote for
	 * @return boolean true - can vote, false otherwise.
	 */
	/*public function canVote($party, $raadsstuk) {

	}*/

	///////////////////////////////////////////////////////
	//                                                   //
	//     HNS.dev SYNCHRONISATION RELATED FUNCTIONS     //
	//                                                   //
	///////////////////////////////////////////////////////

	public function __get($key) {
		switch ($key) {
			case 'hns_initials':
				return $this->fixedFirstName('initials');
			case 'hns_first_name':
				return $this->fixedFirstName('first_name');
			case 'hns_gender':
				return $this->gender_is_male ? 'm' : 'f';
			case 'nonnull_email':
				$email = $this->email;
				return strlen($email) > 0 ? $email : $this->randomKey(16) . '@thisis.never.goingtomatch.gl';
			case 'hns_photo':
				if (!$this->photo) return null;
				///FIXME Replace SERVER_NAME with something sensible (based on config)
				$photo_url = 'http://'. $_SERVER['SERVER_NAME'] .'/images/';
				return  $photo_url. Dispatcher::inst()->activeSite['publicdir'] .'/'. $this->photo;
			default:
				return parent::__get($key);
		}
	}

	public function verifyCanSyncToHns() {
		if (in_array($this->last_name, array( 'Onbekend', 'Test'))) {
			throw new HnsCannotSyncError('Test or Invalid Politician');
		}
		if (is_null($this->first_name) || strlen($this->first_name) == 0) {
			throw new HnsCannotSyncError('Empty first name');
		}

		return parent::verifyCanSyncToHns();
	}

	/**
	 * WSMR DB contains mostly initials in the first_name column. This method tries to
	 * determine if the first_name field actually contains the person's initials.
	 * @param    string     first_name or initials
	 * @return   string     first_name/initials value
	 * @return   null       if data in DB does not match requested field
	 */
	protected function fixedFirstname($requestedField = 'first_name') {
		if ($requestedField == 'first_name') return $this->first_name;

		$typeOfNameInDB = 'first_name';

		if (strlen($this->first_name) == 1) {
			$typeOfNameInDB = 'initials';
		}

		if (preg_match('/^([A-Z]\.)+$/i', $this->first_name)) {
			$typeOfNameInDB = 'initials';
		}

		if ($typeOfNameInDB == $requestedField) return $this->first_name;

		return null;
	}

	protected function getHnsMapping() {
		$mapping = array(
			'hnsTable'   => 'person',
			'fields' => array(
				'hns_initials'      => 'initials',
				'hns_first_name'    => 'usualname',
				'last_name'  => 'lastname',
				'hns_gender' => 'gender',
				'email'      => 'email',
				'hns_photo'  => 'picture'
			)
		);

		return $mapping;
	}

	protected $uniques = array(
		'last_name'     => 'lastname',
		'hns_gender'    => 'gender',
		'nonnull_email' => 'email'
	);

	protected function retrieveMatchingHnsObjects() {
		// Find matching persons based on getHnsUniqueCheck()
		$persons = parent::retrieveMatchingHnsObjects();

		if (!empty($persons)) return $persons;

		// Try to match politician through (unsynced) party appointments
		$appointments = $this->listAllAppointments();

		foreach ($appointments as $appointment) {
			try {
				// Localparties and appointmenst are not consistent in the DB
				$localparty = $appointment->getLocalParty();
			}
			catch (Exception $e) {
				$this->debug($e->getMessage());
				// No valid localparty for appointment, so skip
				continue;
			}

			$politician = $appointment->getPolitician();

			// Try to match localparty to organization in HNSdev
			if (!$localparty->hasHnsId()) {
				$localparty->save();
			}

			$party_name    = $this->sanitizeValue($localparty->party_name);
			$function_name = $this->sanitizeValue($appointment->hns_function_name);

			$query = "
				<query>
					<select>person.lastname</select>
					<select>person.usualname</select>
					<select>person.initials</select>
					<select>organization.area</select>
					<select>function.name</select>
					<from>person_function</from>
					<where>function = '{$function_name}'</where>
					<where>organization = '{$party_name}'</where>
				</query>
			";

			$result = $this->execute($query);

			$persons = array('person' => array());

			if (empty($result['person_function'])) return array();

			foreach ($result['person_function'] as $pers_func) {
				$organization = $pers_func['organization'][0]['organization'][0];
				$function     = $pers_func['function'][0]['functie'][0];
				$person       = $pers_func['person'][0]['person'][0];

				if ($organization['id'] != $localparty->hnsId())   continue;
				if ($function['name']   != $appointment->hns_function_name) continue;
				if ($person['lastname'] != $politician->last_name) continue;

				$persons['person'][] = $person;
			}

			return $persons;
		}
	}

	protected function randomKey($keyLength = 16) {
		$key = '';

		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

		$maxPos = strlen($chars) - 1;

		for ($i = 0; $i < $keyLength; $i++) {
			$key .= $chars[rand(0, $maxPos)];
		}

		return $key;
	}
}

?>
