<?php

require_once('RegionModel.class.php');
require_once('LocalParty.class.php');

/**
* Handles parties.
*
* @author Sardar Yumatov (ja.doma@gmail.com)
*/
class PartyModel {

	/** DB table name containing all parties. */
	const TABLE_NAME = 'pol_parties';
	/** used by lastInertId() to retrieve the last inserted ID. null for MySQL */
	const ID_SEQUENCE = 'pol_parties_id_seq';

	/** party parents. */
	const REFERENCE_TABLE = 'pol_party_parents';
	/** used by lastInertId() to retrieve the last inserted ID. null for MySQL */
	const REFERENCE_SEQUENCE = 'pol_party_parents_id_seq';


	protected $id;
	protected $name;
	protected $key;
	protected $region;
	protected $abbreviation;

	protected $schema;
	protected $db;

	/** @var array (pol_party_parents.id => PartyModel) */
	protected $combi;
	/** @var array (PartyModel.id => PartyModel) */
	protected $revcombi;

	/** @var LocalPartyManager */
	protected $manager;
	
	private $touched = array();

	/**
	 * Construct new unresolved party.
	 *
	 * @param string $name party name
	 * @param RegionModel $region region path
	 * @param string $abbreviation short name
	 */
	public function __construct($name, RegionModel $region, $abbreviation = null) {
		$this->id = null;
		$this->name = $name;
		$this->key = self::stem($name);

		$this->region = $region;
		$this->abbreviation = $abbreviation;
		$this->schema = null;

		$this->combi = array();
		$this->revcombi = array();
	}

	/**
	 * Resolve this object.
	 * This method is for internal use only!
	 *
	 * @access package
	 * @param PartyModelSchema $schema the parent schema
	 * @param PDO $db database link
	 *
	 */
	public function resolve(PartyModelSchema $schema, $db, $id) {
		$this->schema = $schema;
		$this->db = $db;

		if($id === null) {
			if (defined('REGION_ACCESS') && !isset($GLOBALS['REGION_ACCESS_LIST'][$this->getRegion()->getId()]))
				throw new RuntimeException('Not allowed to insert parties for region: '.$this->getRegion()->name);

			$log = JLogger::getLogger("utils.import.schema.party");
			$log->preUpdate("Inserting new party: {$this->name}");

			//all parties are already loaded, so this should be new one
			$ins = $this->db->prepare('INSERT INTO '.self::TABLE_NAME.' (name, combination, owner, short_form) VALUES(:name, :combination, :owner, :short_form)');
			$ins->execute(array(
				':name' => $this->name,
				':combination' => $this->isCombination()? 1: 0,
				':owner' => $this->getRegion()->getId(),
				':short_form' => $this->abbreviation
			));

			if($ins->rowCount() != 1) throw new RuntimeException("Can't insert new party '{$this->getName()}', database error!");
			$id = $this->db->lastInsertId(self::ID_SEQUENCE);
            $this->id = $id;

            $log->postUpdate("Successfully inserted new party: {$this->name}, id: {$id}");
		} else $this->id = $id;

		//[FIXME: now we have PDO mixed with old Record connections... shit...]
		$this->manager = new LocalPartyManager($this->id); //this will cause O(n) queries!
	}


	/**
	 * Add party as parent (this is a combination)
	 * This method is for internal use only!
	 *
	 * @access package
	 * @param integer $id reference id
	 * @param PartyModel $parent parent party
	 * @return void
	 */
	public function resolveParent($id, $parent) {
		$this->combi[$id] = $parent;
		$this->revcombi[$parent->getId()] = $parent;
	}


	/**
	 * Returns record ID of this party.
	 * @return integer
	 */
	public function getId() {
		if($this->id === null) throw new RuntimeException("Party '{$this->name}' is not yet resolved!");
		return $this->id;
	}

	/**
	 * Returns <tt>PartyModelSchema</tt> that handles all the parties.
	 * @return PartyModelSchema
	 */
	public function getSchema() {
		if($this->schema == null) throw new RuntimeException("Party '{$this->name}' is not yet resolved");
		return $this->schema;
	}

	/**
	 * Returns party name.
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Returns stemmed party name.
	 * @return string
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * Returns abbreviation (short name).
	 * @return string null if not set
	 */
	public function getAbbreviation() {
		return $this->abbreviation;
	}

	/**
	 * Returns region where that party was "created" or the biggest area
	 * where this party is present.
	 *
	 * Note: this has nothing to do with party registrations in the regions,
	 * it is just a field in the database that is probably not used anymore...
	 *
	 * @return RegionModel the party region
	 */
	public function getRegion() {
		if(!($this->region instanceof RegionModel)) {
			if($this->schema == null) throw new RuntimeException("Can't obtain region for party '{$this->name}', party is not resolved!");
			$this->region = $this->schema->getGlobalSchema()->getRegionSchema()->getRegion($this->region);
		}
		return $this->region;
	}

	/**
	 * Returns true if this party is a combination of other parties.
	 * @return boolean
	 */
	public function isCombination() {
		if($this->schema == null) throw new RuntimeException("Party '{$this->name}' is not yet resolved");
		return sizeof($this->combi) > 0;

	}

	/**
	 * Returns list of parties that form together this party.
	 * @return array of PartyModel objects
	 */
	public function listCombinationParties() {
		if($this->schema == null) throw new RuntimeException("Party '{$this->name}' is not yet resolved");
		return $this->combi;
	}

	/**
	 * Returns true if given $party is whin the list of parent parties of this party.
     * @param string|PartyModel $party the party to check
     * @return boolean
	 */
	public function isCombinationOf($party) {
		if($this->schema == null) throw new RuntimeException("Party '{$this->name}' is not yet resolved");
		$id =($party instanceof PartyModel)? $party->getId(): $this->schema->getParty($party)->getId();
		return isset($this->revcombi[$id]);
	}


	/**
	 * Ensure party is registered in the $region at the [$date_start - $date_end] time range.
	 * 
	 * This method allows explicit party registration in specific region. The party will be registered
	 * in a region anyway if you try to define a politician working in specific region (or try to vote
	 * in the name of that politician, that implies valid function in related region).
	 * 
	 * Note: this method uses TimeRange rules, that means the invariant: at any given time point there
	 * is no more than one registration; will be maintained, thus existing registrations may be
	 * extended/shrinked/deleted or method may do nothing if given time range is already covered by
	 * another region registration.
	 * 
	 * Warning: giving $date_start = null, $date_end = null you will register the party
	 * in the region for unlimited time.
	 *
	 * @throws RuntimeException on any error
	 * @param RegionModel $region region path where in the party should be registered
	 * @param string $date_start first day as 'YYYY-mm-dd' string, null for 'infinity'
	 * @param string $date_end last day as 'YYYY-mm-dd' string, null for 'infinity'
	 * @param boolean $continue do not commit changes yet, there are more invokations (you should know what you are doing)
	 * @return void
	 */
	public function ensureRegisteredInRegion(RegionModel $region, $date_start, $date_end = null, $continue = false) {
		$log = JLogger::getLogger("utils.import.schema.party");

		if (defined('REGION_ACCESS') && !isset($GLOBALS['REGION_ACCESS_LIST'][$region->getId()]))
			throw new RuntimeException('Not allowed to update parties for region: '.$region->getName());

		if($log->isEnabled(JLogger::ENTER)) $log->enter("Ensuring region registration for party '{$this->name}' in region: {$region->getPath()} for time range: [{$date_start} - {$date_end}]");

		if($this->schema == null) throw new RuntimeException("Party '{$this->name}' is not yet resolved");
		
		if(($start = ModelSchema::normalizeDate($date_start)) === false) throw new InvalidArgumentException("Incorrect start date: '{$date_start}'. Expecting: yyyy-mm-dd");
		else $date_start = $start;
		
		if(($end = ModelSchema::normalizeDate($date_end)) === false) throw new InvalidArgumentException("Incorrect end date: '{$date_start}'. Expecting: yyyy-mm-dd");
		else $date_end = $end;

		//define range
		$this->manager->addRange($region->getId(), $date_start == ''? null: $date_start, $date_end == ''? null: $date_end);
		
		//if($log->isEnabled(JLogger::DEBUG)) $log->debug("Registering party '{$this->name}' in region '{$region->getName()}' for range: [{$date_start} - {$date_end}]");
		if(!$continue) { //play changes
			if($this->manager->hasChanges()) {
				if(defined('DRY_RUN')) throw new RuntimeException("Before registering party: '{$this->name}' in region '{$region->getName()}' for range [$date_start - $date_end] and caused real changes.");				
				
				if($log->isEnabled(JLogger::PRE_UPDATE)) $log->preUpdate("Before registering party '{$this->name}' in region '{$region->getName()}' for range: [{$date_start} - {$date_end}]");
				
				//now we have to do all the changes within current transaction, otherwise:
				//  - we will be unable to rollback transaction
				//  - postgres will complain about non existing records if new party was created
				
				$this->manager->playChangesInForeignPDO($this->db);
				if($log->isEnabled(JLogger::POST_UPDATE)) $log->postUpdate("Changes played successfully. Party '{$this->name}' is in region '{$region->getName()}' for range: [{$date_start} - {$date_end}]");
			}
		}
		if($log->isEnabled(JLogger::LEAVE)) $log->leave("Leaving region registration for party '{$this->name}' in region: {$region->getPath()} for time range: [{$date_start} - {$date_end}]");
	}


	/**
	 * Register this party as a combination of other parties and add such party reference.
	 *
	 * @param string|PartyModel $name the party to add
	 * @return void
	 */
	public function addPartyReference($name) {
		if($this->schema == null) throw new RuntimeException("Party '{$this->name}' is not yet resolved");
		
		$party = $this->schema->getParty(is_string($name)? $name: $name->getName());
		if(isset($this->revcombi[$party->getId()])) return; //already defined

		$log = JLogger::getLogger("utils.import.schema.party");
		$log->debug("Adding party reference '{$party->getName()}' -> '{$this->name}'");
		$log->preSelect("Fetching party parent reference '{$party->getName()}' -> {$this->name}'");

		$stm = $this->db->prepare('SELECT id FROM '.self::REFERENCE_TABLE.' WHERE party = :party AND parent = :parent');
		$stm->execute(array(':party' => $this->id, ':parent' => $party->getId()));

		$row = $stm->fetch(PDO::FETCH_ASSOC);
		if($row) $id = $row['id'];
		else { //insert new reference
			if(defined('DRY_RUN')) throw new RuntimeException("Before inserting party-parent reference: ".$this->name.", parent: ".$party->getName());

			$log->preUpdate("Inserting new party parent reference '{$party->getName()}' -> {$this->name}'");
			$ins = $this->db->prepare('INSERT INTO '.self::REFERENCE_TABLE.'(party, parent) VALUES(:party, :parent);');
			$ins->execute(array(':party' => $this->id, ':parent' => $party->getId()));

			if($ins->rowCount() != 1) throw new RuntimeException("Can't register party parent reference '{$this->name}' to '{$party->getName()}'.");
			$id = $this->db->lastInsertId(self::REFERENCE_SEQUENCE);

			$log->postUpdate("Successfully inserted new party parent reference {$id} -- '{$party->getName()}' -> {$this->name}'");

			$log->preUpdate("Updating party combination flag for party '{$this->name}'");
			$up = $this->db->prepare('UPDATE '.self::TABLE_NAME.' SET combination = 1 WHERE id = :id ;');
			$up->execute(array(':id' => $this->getId()));
			if($ins->rowCount() != 1) throw new RuntimeException("Can't change combination flag of party '{$this->name}'.");
			$log->postUpdate("Party combination flag for party '{$this->name}' is successfully set.");
		}

		$this->combi[$id] = $party;
		$this->revcombi[$party->getId()] = $party;
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
	 * Serialize this party to the DOM tree.
	 *
	 * @param DOMDocument $dom the owner document, used to create elements
	 * @param DOMElement $root where to 'party' element will be added
	 * @param array $options extra options
	 * @return void
	 */
	public function toXml($dom, $root, $options) {
		$el = $dom->createElement('party');
		$el->setAttribute('name', $this->name);
		$el->setAttribute('region', $this->getRegion()->getPath());
		if($this->abbreviation) $el->setAttribute('abbreviation', $this->abbreviation);
		$root->appendChild($el);


		if($this->isCombination()) {
			$combi = $dom->createElement('combination');

			foreach ($this->combi as $cm) {
				$c = $dom->createElement('partyref');
				$c->setAttribute('party', $cm->getName());
				$combi->appendChild($c);
			}

			$el->appendChild($combi);
		}

		$regs = $this->schema->getGlobalSchema()->getRegionSchema();
		
		$parts = $this->manager->listLocalParties();
		foreach ($parts as $loc) {
			$r = $dom->createElement('inregion');
			$r->setAttribute('region', $regs->lookup($loc->region)->getPath());
			list($start, $end) = TimeRange::postgresTimes($loc->time_start, $loc->time_end);
			if($start != '') $r->setAttribute('date_start', $start);
			if($end != '') $r->setAttribute('date_end', $end);
			$el->appendChild($r);
		}
		
		/*
		foreach ($this->inregions as $regid => $reg) {
			$rr = $regs->lookup($regid);

			foreach ($reg as $tm) {
				$r = $dom->createElement('inregion');
				$r->setAttribute('region', $rr->getPath());
				if($tm['start'] != '') $r->setAttribute('date_start', $tm['start']);
				if($tm['end'] != '') $r->setAttribute('date_end', $tm['end']);
				$el->appendChild($r);
			}
		} */
	}
	
	/**
	 * Serialize this category to the XML stream.
	 *
	 * @param XMLWriter $xw XML output stream
	 * @param array $options extra options
	 * @return void
	 */
	public function toXmlWrite($xw, $options = null) {
		if(!$this->schema->isTraced($this->id)) return;
		
		$xw->startElement('party'); // <party>
		$xw->writeAttribute('name', $this->name);
		$xw->writeAttribute('region', $this->getRegion()->getPath());
		if($this->abbreviation) $xw->writeAttribute('abbreviation', $this->abbreviation);
		
		if($this->isCombination()) {
			$xw->startElement('combination'); // <combination>

			foreach ($this->combi as $cm) {
				$xw->startElement('partyref'); // <partyref>
				$xw->writeAttribute('party', $cm->getName());
				$xw->endElement(); // </partyref>
			}

			$xw->endElement(); // </combination>
		}

		$regs = $this->schema->getGlobalSchema()->getRegionSchema();
		
		$parts = $this->manager->listLocalParties();
		foreach ($parts as $loc) {
			if($this->schema->isTracing() && !isset($this->touched[$loc->region])) continue;
			
			$xw->startElement('inregion'); // <inregion>
			$xw->writeAttribute('region', $regs->lookup($loc->region)->getPath());
			list($start, $end) = TimeRange::postgresTimes($loc->time_start, $loc->time_end);
			if($start != '') $xw->writeAttribute('date_start', $start);
			if($end != '') $xw->writeAttribute('date_end', $end);
			$xw->endElement(); // </inregion>
		}
		
		$xw->endElement(); // </party>
	}
	
	
	public function touch() {
		$this->schema->trace($this->id);
		$this->region->touch();
		
		if($this->isCombination()) {
			foreach ($this->combi as $cm) $cm->touch();
		}
	}
	
	public function touchInRegion($regid) {
		$this->touched[$regid] = true;
	}
}


?>
