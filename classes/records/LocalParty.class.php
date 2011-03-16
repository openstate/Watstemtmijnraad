<?php
require_once('Record.class.php');
require_once('HNSSyncedRecord.class.php');

/**
* Party registration in a region.
* This registration is used to limit list of parties to specific regions.
* The [time_start:time_end] is derrived from Appointment [time_start:time_end] ranges
* (containing all).
*/
class LocalParty extends HNSSyncedRecord {
	protected $data = array(
		'party'       => null,
		'party_name'  => null,
		'party_short' => null,
		'region'      => null,
		'region_name' => null,
		'level'       => null,
		'parent'      => null,
		'level_name'  => null,
		'bo_user'     => null,
		'user'        => null,
		'time_start'  => null,
		'time_end'    => null,
	);
	protected $extraCols = array(
		'party_name'  => 'p.name',
		'party_short' => 'p.short_form',
		'region_name' => 'r.name',
		'level'       => 'r.level',
		'parent'      => 'r.parent',
		'level_name'  => 'l.name',
		'user'        => 'u.username',
	);
	protected $multiTables = '
		pol_party_regions t
		JOIN pol_parties p ON t.party = p.id
		JOIN sys_regions r ON r.id = t.region
		JOIN sys_levels l ON r.level = l.id
		LEFT JOIN usr_bo_users u ON t.bo_user = u.id';
	protected $tableName = 'pol_party_regions';


	/**
	 * Returns party registration for specific party/region.
	 * @param Party|integer $party associated party
	 * @param Region|integer $region associated region
	 * @return LocalParty the first in the list
	 */
	public static function loadLocalParty($party, $region) {
		return reset(self::listByPartyRegion($party, $region));
	}

	/**
	 * Returns party registration for specific party/region.
	 * @param Party|integer $party associated party
	 * @param Region|integer $region associated region, null - for all regions
	 * @return array of LocalParty
	 */
	public static function listByPartyRegion($party, $region = null) {
		$ls = new LocalParty();
		//should be part of Party
		return $ls->getList('', $ls->db->formatQuery('WHERE now() < time_end AND party = %i'.($region === null? '': ' AND region = %i'), _id($party), _id($region)), 'ORDER BY p.name');
	}

	public function loadByRegion($region) {
		$list = array();
		foreach ($this->getList('', 'WHERE now() < time_end AND region = '.intval($region)) as $lp) {
			$list[$lp->party] = $lp;
		}
		return $list;
	}

	/**
	 * Ensures party date boundary is not over the containing politician date boundary.
	 * @param string $date date in 'YYYY-mm-dd' format
	 */
	public function setStartDate($date) {
		if($this->id) { //we do exists
			require_once('Appointment.class.php');
			$ap = new Appointment();
			$lst = $ap->getList('', $this->db->formatQuery('WHERE t.party = %i AND t.region = %i AND t.time_start < %s', $this->party, $this->region, $date), 'ORDER BY t.time_start ASC');
			if(count($lst) > 0) { //no, we can't, there is a politician function which we will exclude
				$bound = reset($lst);
				$date = $bound->time_start;
			}
		}
		$this->time_start = $date;
		return $date;
	}

	/**
	 * Ensures party date boundary is not over the containing politician date boundary.
	 * @param string $date date in 'YYYY-mm-dd' format
	 */
	public function setEndDate($date) {
		if($this->id) { //we do exists
			require_once('Appointment.class.php');
			$ap = new Appointment();
			$lst = $ap->getList('', $this->db->formatQuery('WHERE t.party = %i AND t.region = %i AND t.time_end > %s', $this->party, $this->region, $date), 'ORDER BY t.time_end DESC');
			if(count($lst) > 0) { //no, we can't, there is a politician function which we will exclude
				$bound = reset($lst);
				$date = $bound->time_end;
			}
		}
		$this->time_end = $date;
		return $date;
	}


	/**
	 * Returns largest time_start of all politician functions (appointments) registered with this party.
	 *
	 * @return string boundary date as 'yyyy-mm-dd' or null if there is no registered politician function
	 */
	public function getStartBoundary() {
		require_once('Appointment.class.php');
		$ap = new Appointment();
		$lst = $ap->getList('', $this->db->formatQuery('WHERE t.party = %i AND t.region = %i', $this->party, $this->region), 'ORDER BY t.time_start ASC', 'LIMIT 1');
		return count($lst)? reset(explode(' ', reset($lst)->time_start)): null;
	}


	/**
	 * Returns largest time_end of all politician functions (appointments) registered with this party.
	 *
	 * @return string boundary date as 'yyyy-mm-dd' or null if there is no registered politician function
	 */
	public function getEndBoundary() {
		require_once('Appointment.class.php');
		$ap = new Appointment();
		$lst = $ap->getList('', $this->db->formatQuery('WHERE t.party = %i AND t.region = %i', $this->party, $this->region), 'ORDER BY t.time_end DESC', 'LIMIT 1');
		return count($lst)? reset(explode(' ', reset($lst)->time_end)): null;
	}


	// formats name: adds level name to provincial and municipal regions
	public function formatRegionName() {
		return ($this->level > 2 ? $this->level_name.' ' : '').$this->region_name;
	}


	/**
	 * When appointment is modified, ensure the party includes modified range.
	 * Should be called each time time_start, time_end of the region changes.
	 *
	 * @param Party|integer $party associated party
	 * @param Region|integer $region associated region
	 * @param Appointment $appointment the modified appointment
	 * @return boolean true if local party was changed, false otherwise
	 */
	public function ensureSyncWithAppointments($party, $region, $appointment) {
		require_once('TimeRange.class.php');

		$range = new TimeRange();
		$regs = self::listByPartyRegion(_id($party), _id($region));

		foreach ($regs as $loc) {
			list($start, $end) = TimeRange::postgresTimes($loc->time_start, $loc->time_end);
			$range->addRange($loc->id, $start, $end);
		}

		list($start, $end) = TimeRange::postgresTimes($appointment->time_start, $appointment->time_end);
		$range->addContentRange($start, $end);

		$changes = $range->playChanges();
		$changed = false;
		$patch = array();
		foreach ($changes as $chg) {
			$changed = true;
			$pr = new LocalParty();
			if($chg['id'] > 0) $pr->load($chg['id']);
			if($chg['action'] == 'delete') {
				echo "Deleting local party";
				$pr->delete();
			} else {
				$pr->party = _id($party);
				$pr->region = _id($region);
				$pr->time_start = $chg['start']? $chg['start']: '-infinity';
				$pr->time_end = $chg['end']? $chg['end']: 'infinity';
				$pr->save();

				$patch[$chg['id']] = $pr->id;
			}
		}

		$range->clearChanges($patch);
		return $changed;
	}


	///////////////////////////////////////////////////////
	//                                                   //
	//     HNS.dev SYNCHRONISATION RELATED FUNCTIONS     //
	//                                                   //
	///////////////////////////////////////////////////////

	public function getByAppointmentId($appointmentId) {
		$sql = "
			SELECT
				lp.*
			FROM
				pol_party_regions lp,
				pol_politician_functions pf
			WHERE
				pf.region = lp.region
				AND pf.party = lp.party
				AND pf.id = {$appointmentId}
				AND pf.time_start >= lp.time_start
				AND pf.time_end <= lp.time_end
		";

		$rows = $this->db->query($sql)->fetchAllRows();

		if (count($rows) == 0) return null;

		$localParty = new LocalParty();
		$localParty->loadFromArray($rows[0]);

		return $localParty;
	}

	public function getParentNames() {
		$sql = "
			SELECT
				p.name
			FROM
				pol_party_parents AS pp,
				pol_parties AS p
			WHERE
				pp.party = {$this->id}
				AND pp.parent = p.id
		";

		$rows = $this->db->query($sql)->fetchAllRows();

		$names = array();

		foreach ($rows as $row) {
			$names[] = $row['name'];
		}

		return $names;
	}

	public function __get($name){
		switch ($name){
			case 'hns_organization_type':
				return 'Partij';
			case 'hns_region_id':
				$region = new Region();
				$region->load($this->region);

				if(!$region->hasHnsId()){
					$region->save();
				}
				return $region->hnsId();
			case 'party_name':
				$party = new Party($this->party);
				return $party->name;
			case 'hns_description':
				$names = $this->getParentNames();

				if (empty($names)) return null;

				return 'Partijverbintenis: '. implode(', ', $names);
			default:
				return parent::__get($name);
		}
	}

	protected function getHnsMapping() {
		$mapping = array(
			'hnsTable' => 'organization',
			'fields'   => array(
				'party_name' 			=> 'name',
				'hns_organization_type' => 'type',
		        'hns_region_id'         => 'area',
				'hns_description'       => 'description'
			),
		);

		return $mapping;
	}

	protected $uniques = array(
		'party_name'    => 'name',
		'hns_organization_type' => 'type',
		'hns_region_id' => 'area.id',
	);

	/**
	 * (non-PHPdoc)
	 * @see classes/database/HNSSyncedRecord#insertHnsEntry()
	 */
	protected function insertHnsEntry(){
		$query = '<insert><'.$this->hnsTable().'>
			<name>'.$this->sanitizeValue($this->party_name).'</name>
			<type>'.$this->hns_organization_type.'</type>
			<area><region id="'.$this->hns_region_id.'" /></area>
		</'.$this->hnsTable().'></insert>';

		$hns_organization_id = parent::insertHnsEntry($query);

		//Add party to organization
		$partyQuery = '<insert><party>
			<name>'.$this->sanitizeValue($this->party_name).'</name>
			<organization>'.$hns_organization_id.'</organization>
		</party></insert>';

		parent::insertHnsEntry($partyQuery, 'party');

		return $hns_organization_id;
	}
}


class LocalPartyManager {

	private $party;
	private $localparties;
	private $ranges = array();

	private $touched = array();

	/**
	 * Construct manager for specific party.
	 * @param Party|$integer $party associated party for selection
	 */
	public function __construct($party) {
		require_once('TimeRange.class.php');

		$this->party = _id($party);

		//build ranges
		$pr = new LocalParty();
		$this->localparties = $pr->getList('', 'WHERE party = '.$this->party, 'ORDER BY p.name');

		foreach ($this->localparties as $party) {
			if(!isset($this->ranges[$party->region])) $this->ranges[$party->region] = new TimeRange();
			list($start, $end) = TimeRange::postgresTimes($party->time_start, $party->time_end);
			$this->ranges[$party->region]->addRange($party->id, $start, $end);
		}
	}

	/**
	 * Delete specified range/LocalParty.
	 * Note: you should not play changes if method returns non empty list.
	 *
	 * @param LocalParty|integer $id the local party to delete
	 * @param array of Appointment list of appointments affected by deletion
	 */
	public function deleteRange($id) {
		$lp = $id;
		if(!is_object($lp)) {
			$lp = new LocalParty();
			$lp->load(intval($id));
		}

		$this->ranges[$lp->region]->deleteRange($id);
		return $this->checkContent();

	}

	/**
	 * Insert new range.
	 *
	 * @param Region|integer $region region id
	 * @param string $start start date in 'yyyy-mm-dd'  format
	 * @param string $end end date in 'yyyy-mm-dd' format
	 * @return boolean true if ranges were changed
	 */
	public function addRange($region, $start, $end) {
		$regid = _id($region);
		if(!isset($this->ranges[$regid])) $this->ranges[$regid] = new TimeRange();
		return $this->ranges[$regid]->addRange(null, $start, $end);
	}

	/** Returns all LocalParty objects used to create the ranges. */
	public function listLocalParties() {
		if($this->localparties == null) {
			$pr = new LocalParty();
			$this->localparties = $pr->getList('', 'WHERE party = '.$this->party, 'ORDER BY p.name');
		}
		return $this->localparties;
	}


	/**
	 * Check of all Appointments fit in the ranges.
	 *
	 * Note: this method changes internal ranges, such that play changes
	 * will commit ranges including content ranges.
	 *
	 * @return array of Appointment list of appointments that don't fit
	 */
	public function checkContent() {
		//this method should not have side effect, but anyway, this is implemented and usable.
		$constrained = array();
		$apps = Appointment::listByParty($this->id);
		foreach ($apps as $ap) {
			if(!isset($this->ranges[$ap->region])) $this->ranges[$ap->region] = new TimeRange(); //warning: fix range
			list($start, $end) = TimeRange::postgresTimes($ap->time_start, $ap->time_end);
			if($this->time_rangrangess[$ap->region]->addContentRange($start, $end)) $constrained[] = $ap;
		}

		return $constrained;
	}

	/**
	 * Content changed, ensure ranges stay consistent.
	 * @param Appointment $appointment
	 * @param boolean true - local party was changed, false - otherwise
	 */
	public function ensureSyncWithAppointment(Appointment $appointment) {
		list($start, $end) = TimeRange::postgresTimes($appointment->time_start, $appointment->time_end);
		return $this->ranges[$appointment->region]->addContentRange($start, $end);
	}

	/**
	 * Content changed, ensure ranges stay consistent.
	 * @param integer $region associated region
	 * @param string $start start date as 'yyyy-mm-dd'
	 * @param string $end start date as 'yyyy-mm-dd'
	 * @param boolean true - local party was changed, false - otherwise
	 */
	public function ensureSyncWithRange($region, $start, $end) {
		return $this->ranges[_id($region)]->addContentRange($start, $end);
	}

	/**
	 * Returns true if there are pending changes.
	 * @return boolean
	 */
	public function hasChanges() {
		foreach ($this->ranges as $region => $rng) { //commit changes
			$changes = $rng->playChanges();
			if(!empty($changes)) return true;
		}

		return false;
	}

	/**
	 * Play all the changes.
	 * @return boolean true - real changes commited, false - no changes
	 */
	public function playChanges() {
		$changed = false;
		$patch = array();
		foreach ($this->ranges as $region => $rng) { //commit changes
			$changes = $rng->playChanges();
			foreach($changes as $chg) {
				$changed = true;
				$pr = new LocalParty();
				if($chg['id'] > 0) $pr->load($chg['id']);
				if($chg['action'] == 'delete') {
					$pr->delete();
				} else {
					$pr->party = $this->party;
					$pr->region = $region;
					$pr->time_start = $chg['start']? $chg['start']: '-infinity';
					$pr->time_end = $chg['end']? $chg['end']: 'infinity';
					$pr->save();

					$patch[$chg['id']] = $pr->id;
				}
			}
			$rng->clearChanges($patch);
		}

	    if($changed) $this->localparties = null; //refresh
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
	public function playChangesInForeignPDO($pdo) {
		$insert = $pdo->prepare("INSERT INTO pol_party_regions(party, region, time_start, time_end)
		                         VALUES(:party, :region, :time_start, :time_end)");

		$update = $pdo->prepare("UPDATE pol_party_regions SET
										party = :party,
										region = :region,
										time_start = :time_start,
										time_end = :time_end
								 WHERE id = :id");

		$delete = $pdo->prepare("DELETE FROM pol_party_regions WHERE id = :id");

		$changed = false;
		$patch = array();
		foreach ($this->ranges as $region => $rng) { //commit changes
			$changes = $rng->playChanges();
			foreach($changes as $chg) {
				$changed = true;

				if($chg['action'] == 'delete') {
					if($chg['id'] < 0) throw new RuntimeException("Trying to delete non existing party-region registration: {$chg['id']}. Time-range id handling bug detected.");
					$delete->execute(array(':id' => $chg['id']));
					if($delete->rowCount() != 1) throw new RuntimeException("Can't delete party ({$this->party}) region ({$region}) registration ({$chg['id']}), database error!");
				} else {
					$data = array(
						':region' => $region,
						':party' => $this->party,
						':time_start' => $chg['start']? $chg['start']: '-infinity',
						':time_end' => $chg['end']? $chg['end']: 'infinity'
					);

					if($chg['action'] == 'update') {
						if($chg['id'] < 0) throw new RuntimeException("Trying to update non existing party-region registration: {$chg['id']}. Time-range id handling bug detected.");
						$data[':id'] = $chg['id'];
						$update->execute($data);
						if($update->rowCount() != 1) throw new RuntimeException("Can't update party ({$this->party}) region ({$region}) registration ({$chg['id']}) [{$chg['start']} - {$chg['end']}], database error!");
					} else {
						$insert->execute($data);
						if($insert->rowCount() != 1) throw new RuntimeException("Can't insert new party ({$this->party}) region ({$region}) registration [{$chg['start']} - {$chg['end']}], database error!");
						$patch[$chg['id']] = $pdo->lastInsertId('pol_party_regions_id_seq');
					}
				}
			}
			$rng->clearChanges($patch);
		}

	    if($changed) $this->localparties = null; //refresh
	    return $changed;
	}
}

?>