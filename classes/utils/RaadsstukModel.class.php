<?php

require_once('NotFoundException.class.php');
require_once('RegionModel.class.php');
require_once('ExportQueryBuilder.class.php');

/**
* Handles raadsstukken.
*
* @author Sardar Yumatov (ja.doma@gmail.com)
*/
class RaadsstukModel {
	/** DB table name containing all raadsstukken. */
	const TABLE_NAME = 'rs_raadsstukken';
	/** used by lastInertId() to retrieve the last inserted ID. null for MySQL */
	const ID_SEQUENCE = 'rs_raadsstukken_id_seq';
	/** DB table name containing all categories links */
	const CATEGORIES_TABLE = 'rs_raadsstukken_categories';
	/** DB table name containing all tag links */
	const TAGS_TABLE = 'rs_raadsstukken_tags';


	/** Mapping (external_id => internal_id) */
	protected static $raadsstukken = array();

	private $schema;
	private $db;

	private $site;
	private $title;
	private $vote_date;
	private $region;
	private $summary = null;

	private $code = null;
	private $show = false;
	private $extid = null;
	private $localid = null;

	private $tags = array();
	private $categories = array();

	private $submitter = null;
	private $voter = null;

	/**
	 * Construct new raadsstuk (import)
	 *
	 * @param ModelSchema site schema
	 * @param PDO $db database link
	 * @param integer $site site id
	 * @param string $title raadsstuk title
	 * @param string $vote_date 'yyyy-mm-dd' or 'dd
	 * @param RegionModel $region region path
	 * @param string $code optional code
	 * @param boolean $show show on home page
	 * @param string $extid external id
	 * @param string $localid our local id
	 */
	public function __construct(ModelSchema $schema, $db, $site, $title, $vote_date, RegionModel $region, $code = null, $show = false, $extid = null, $localid = null) {
		$this->schema = $schema;
		$this->db = $db;

		$vdate = ModelSchema::normalizeDate($vote_date);
		if(!$vdate)	throw new InvalidArgumentException("Incorrect vote date, expecting 'yyyy-mm-dd' or 'dd-mm-yyyy', got: {$vote_date}");
		$this->vote_date = $vdate;

		$this->region = $region;
		$this->site = $site;
		$this->title = $title;
		$this->code = $code;
		$this->show = $show;
		$this->extid = $extid;
		$this->summary = '';
		$this->localid = $localid? intval($localid): null;
	}


	/** Handles read only properties of this class. */
	public function __get($name) {
		switch ($name) {
			case 'title': return $this->title;
			case 'voteDate': return $this->vote_date;
			case 'region': return $this->region;
			case 'code': return $this->code;
			case 'show': return $this->show;
			case 'externalId': return $this->extid;
			case 'summary': return $this->summary;
			case 'schema': return $this->schema;

			default:
				throw new RuntimeException('Unsupported field: '.$name);
		}
	}

	/** Set raadsstuk summary text. */
	public function setSummary($text) {
		$this->summary = $text;
	}

	/**
	 * Assign tag.
	 * @param TagModel $tag
	 */
	public function addTag(TagModel $tag) {
		$this->tags[$tag->getName()] = $tag->getId();
	}

	/** List all assigned tags. */
	public function listTags() {
		return array_keys($this->tags);
	}

	/**
	 * Assign category.
	 * @param CategoryModel $category
	 */
	public function addCategory(CategoryModel $category) {
		$this->categories[$category->getName()] = $category->getId();
	}

	/** List all assigned categories. */
	public function listCategories() {
		return array_keys($this->categories);
	}

	/**
	 * Define submitting procedure.
	 *
	 * @param string $type submitting type
	 * @param string $submitter submitter
	 * @return SubmitterProcedure
	 */
	public function defineSubmitter($type, $submitter = null) {
		$this->submitter = SubmitterProcedure::forType($this, $this->schema, $this->db, $type, $submitter);
		return $this->submitter;
	}

	/**
	 * Returns defined submitter procedure.
	 * Submitter must be defined prior to calling this method.
	 *
	 * @throws RuntimeException if submitter block is not yet defined
	 * @return SubmitterProcedure
	 */
	public function getSubmitter() {
		if($this->submitter == null) throw new RuntimeException("Invalid state, submitter is not yet defined!");
		return $this->submitter;
	}


	/**
	 * Define vote block object.
	 * Vote block must be defined prior to calling this method.
	 *
	 * @param string $type either 'party' or 'politician'
	 * @param string $result one of 'new', 'declined', 'accepted'
	 * @return RaadsstukVoteBlock
	 */
	public function defineVoteBlock($type, $result = null) {
		$this->voter = RaadsstukVoteBlock::forType($this, $this->schema, $this->db, $type, $result);
		return $this->voter;
	}

	/**
	 * Returns defined vote block.
	 * @throws RuntimeException if vote block is not yet defined
	 * @return RaadsstukVoteBlock
	 */
	public function getVoteBlock() {
		if($this->voter == null) throw new RuntimeException("Invalid state, vote block is not yet defined!");
		return $this->voter;
	}

	/**
	 * Insert data to the database.
	 * 
	 * Update:
	 * If localid was specified, then lookup for the given id. If the raadsstukk was not found, then fail.
	 * Otherwise overwrite existing raadsstuk with new data.
	 *
	 * @throws RuntimeException on any error
	 * @return void
	 */
	public function create() {
		$log = JLogger::getLogger('utils.import.raadsstuk');

		if (defined('REGION_ACCESS') && !isset($GLOBALS['REGION_ACCESS_LIST'][$this->region->getId()]))
			throw new RuntimeException('Not allowed to insert documents for region: '.$this->region->getName());

		if(defined('DRY_RUN')) throw new RuntimeException("Before inserting new raadsstuk: ".$this->title);
		
		static $upd = null;
		if($upd == null) $upd = $this->db->prepare('UPDATE '.self::TABLE_NAME.' SET region = :region, title = :title, vote_date = :vote_date, summary = :summary, code = :code, type = :type, result = :result, submitter = :submitter, parent = :parent, show = :show, site_id = :site_id WHERE id = :id ;');
		
		static $ins = null;
		if($ins == null) $ins = $this->db->prepare('INSERT INTO '.self::TABLE_NAME.'(region, title, vote_date, summary, code, type, result, submitter, parent, show, site_id) VALUES(:region, :title, :vote_date, :summary, :code, :type, :result, :submitter, :parent, :show, :site_id);');
		
		static $sel_exact = null;
		if($sel_exact == null) $sel_exact = $this->db->prepare('SELECT id, region FROM '.self::TABLE_NAME.' WHERE id = :id');
		
		$raddat = array(
			':region' => $this->region->getId(),
			':title' => $this->title,
			':vote_date' => $this->vote_date,
			':summary' => $this->summary,
			':code' => $this->code,
			':type' => $this->getSubmitter()->getTypeRef(),
			':submitter' => $this->getSubmitter()->getSubmitterRef(),
			':parent' => $this->submitter->getParentRaadstukId(),
			':result' => $this->getVoteBlock()->getResult(),
			':show' => $this->show? 1: 0,
			':site_id' => $this->site->getId()
		);
		
		if($this->localid) { //overwrite
			$rad = $sel_exact->execute(array(':id' => $this->localid));
			if(!$rad || !($rad = $sel_exact->fetch(PDO::FETCH_ASSOC)))
				throw new RuntimeException("Can't update raadsstuk {$this->localid}, it doesn't exist!");

			if (defined('REGION_ACCESS') && !isset($GLOBALS['REGION_ACCESS_LIST'][$rad['region']]))
				throw new RuntimeException("Not allowed to update documents for region id: {$rad['region']}");
			
			$raddat[':id'] = $this->localid;
			$log->preUpdate("Updating existing raadsstuk: {$this->localid}");
			$upd->execute($raddat);
			if($upd->rowCount() != 1) throw new RuntimeException("Can't update raadsstuk {$this->localid}, database failed!");
			$id = $this->localid;
			$log->postUpdate("Successfully updated raadsstuk: {$this->localid}");			
		} else {
			$log->preUpdate("Inseting new raadsstuk: {$this->title}");
			$ins->execute($raddat);
			if($ins->rowCount() != 1) throw new RuntimeException("Can't insert new raadsstuk '{$this->title}', database failed!");
			$id = $this->db->lastInsertId(self::ID_SEQUENCE);
			$log->postUpdate("Successfully insterted new raadsstuk: {$this->title}, id {$id}");
		}		
		if($this->extid) self::$raadsstukken[$this->extid] = $id;

		static $delcats = null;
		if($delcats == null) $delcats = $this->db->prepare('DELETE FROM '.self::CATEGORIES_TABLE.' WHERE raadsstuk = :raadsstuk');
		
		if($this->localid) {
			$log->preUpdate("Deleteting raadsstuk id: {$id} from all categories");
			$delcats->execute(array(':raadsstuk' => $this->localid));
			$log->postUpdate("Successfully unlinked all categories of raadsstuk id: {$id}");
		}
		
		$log->preUpdate("Registering raadsstuk id: {$id} with ".sizeof($this->categories)." categories.");
		static $cats = null;
		if($cats == null) $cats = $this->db->prepare('INSERT INTO '.self::CATEGORIES_TABLE.'(raadsstuk, category) VALUES(:raadsstuk, :category)');

		foreach ($this->categories as $catid) {
			$cats->execute(array(':raadsstuk' => $id, ':category' => $catid));
		}
		$log->postUpdate("Successfully linked categories with raadsstuk id: {$id}");

		static $deltags = null;
		if($deltags == null) $deltags = $this->db->prepare('DELETE FROM '.self::TAGS_TABLE.' WHERE raadsstuk = :raadsstuk');
		
		if($this->localid) {
			$log->preUpdate("Unlinking all tags of raadsstuk id: {$id}");
			$deltags->execute(array(':raadsstuk' => $this->localid));
			$log->postUpdate("Successfully cleared all tags of raadsstuk id: {$id}");
		}
		
		$log->preUpdate("Registering ".sizeof($this->tags)." tags for raadsstuk id: {$id}");
		static $tags = null;
		if($tags == null) $tags = $this->db->prepare('INSERT INTO '.self::TAGS_TABLE.'(raadsstuk, tag) VALUES(:raadsstuk, :tag)');

		foreach ($this->tags as $tagid) {
			$tags->execute(array(':raadsstuk' => $id, ':tag' => $tagid));
		}
		$log->postUpdate("Successfully registered all tags for raadsstuk id: {$id}");

		$this->getSubmitter()->install($id, !empty($this->localid));
		$this->getVoteBlock()->install($id, !empty($this->localid));
		
		return $id;
	}

	/**
	 * Lookup for raadstuk using exact data match before inserting.
	 * 
	 * Update:
	 * If local id is specified, then lookup for the raadsstuk. If it is found, then merge the data with id (update mode).
	 * If it is not found, then create new one.
	 * 
	 * 
	 * @throws RuntimeException on any error
	 * @return void
	 */
	public function merge() {
		//lookup for data
		static $sel_np = null;
		static $sel_p = null;
		static $update = null;
		static $sel_exact = null;

		$log = JLogger::getLogger('utils.import.raadsstuk');
		
		if (defined('REGION_ACCESS') && !isset($GLOBALS['REGION_ACCESS_LIST'][$this->region->getId()]))
			throw new RuntimeException('Not allowed to merge documents for region: '.$this->region->getName());

		$parent = $this->submitter->getParentRaadstukId();
		if($sel_np == null) $sel_np = $this->db->prepare('SELECT r.id, r.result, r.show, r.region FROM rs_raadsstukken r WHERE r.region = :region     AND r.title = :title
		                                                      AND r.vote_date = :vote_date  AND r.summary = :summary
		                                                      AND r.code = :code            AND r.type = :type
		                                                      AND r.submitter = :submitter  AND r.site_id = :site_id AND r.parent IS NULL');
		
		if($sel_p == null) $sel_p = $this->db->prepare('SELECT r.id, r.result, r.show, r.region FROM rs_raadsstukken r WHERE r.region = :region     AND r.title = :title
		                                                      AND r.vote_date = :vote_date  AND r.summary = :summary
		                                                      AND r.code = :code            AND r.type = :type
		                                                      AND r.submitter = :submitter  AND r.site_id = :site_id AND r.parent = :parent');
		
		if($sel_exact == null) $sel_exact = $this->db->prepare('SELECT r.id, r.result, r.show, r.region FROM rs_raadsstukken r WHERE id = :id');
		
		
		$sel = $this->localid? $sel_exact: ($parent == null? $sel_np: $sel_p);
		
		$data = $this->localid? array(':id' => $this->localid): array(
			':region' => $this->region->getId(),
			':title' => $this->title,
			':vote_date' => $this->vote_date,
			':summary' => $this->summary,
			':code' => $this->code,
			':type' => $this->getSubmitter()->getTypeRef(),
			':submitter' => $this->getSubmitter()->getSubmitterRef(),
			':site_id' => $this->site->getId()
		);
		if(empty($this->localid) && $parent != null) $data[':parent'] = $parent;
		
		$log->debug("Looking for existing raadsstuk " . ($this->localid? "id: {$this->localid}": "'{$this->title}'"));
		if(!$sel->execute($data)) throw new RuntimeException("Database error. Can't fetch raadsstuk.");
		
		if(!($rad = $sel->fetch(PDO::FETCH_ASSOC))) {
			$log->debug("Raadsstuk not found, creating new one.");
			return $this->create(); //create new raadsstuk
		} else { //merge data
			if (defined('REGION_ACCESS') && !isset($GLOBALS['REGION_ACCESS_LIST'][$rad['region']]))
				throw new RuntimeException("Not allowed to merge documents in region: {$rad['region']}");
			
			$log->debug("Found existing raadsstuk: {$rad['id']}. Merging contents.");
			if($rad['result'] != $this->getVoteBlock()->getResult() || $rad['show'] != $this->show) {
				$log->preUpdate("Changing result state to: {$this->getVoteBlock()->getResult()} and show state to: ".($this->show? 'yes': 'no'));
				
				if($update == null) {
					$update = $this->db->prepare('UPDATE rs_raadsstukken SET result = :result, show = :show WHERE id = :id');
				}
				
				if(defined('DRY_RUN')) throw new RuntimeException("Before updating raadsstuk: ".$rad['id']);
				$update->execute(array(
					':id' => $rad['id'],
					':result' => $this->getVoteBlock()->getResult(),
					':show' => $this->show? 1: 0
				));
				
				if($update->rowCount() != 1) throw new RuntimeException("Can't update raadstuk: {$rad['id']}, database error!");
				$log->postUpdate("Successfully updated existing raadsstuk: {$rad['id']}");
			}
			
			//merge categories
			$log->preUpdate("Merging categories of raadsstuk: {$rad['id']} with ".sizeof($this->categories)." categories from import.");
			static $selcats = null;
			if($selcats == null) $selcats = $this->db->prepare('SELECT category FROM '.self::CATEGORIES_TABLE.' WHERE raadsstuk = :radid');
			
			$selcats->execute(array(':radid' => $rad['id']));
			$existing_categories = array();
			foreach ($selcats as $ct) $existing_categories[$ct['category']] = true;
			
			static $cats = null;
			if($cats == null) $cats = $this->db->prepare('INSERT INTO '.self::CATEGORIES_TABLE.'(raadsstuk, category) VALUES(:raadsstuk, :category)');

			$i = 0;
			foreach ($this->categories as $catid) {
				if(!isset($existing_categories[$catid])) {
					if(defined('DRY_RUN')) throw new RuntimeException("Before assigning category {$catid} to raadsstuk: {$rad['id']}");
					$cats->execute(array(':raadsstuk' => $rad['id'], ':category' => $catid));
					$i += 1;
				}
			}
			unset($existing_categories);
			$log->postUpdate($i == 0? "No new categories were added to raadsstuk: {$rad['id']}": "Successfully linked {$i} new categories with raadsstuk id: {$rad['id']}");
			
			//merge categories
			$log->preUpdate("Merging tags of raadsstuk: {$rad['id']} with ".sizeof($this->tags)." categories from import.");
			static $seltags = null;
			if($seltags == null) $seltags = $this->db->prepare('SELECT tag FROM '.self::TAGS_TABLE .' WHERE raadsstuk = :radid');
			
			$seltags->execute(array(':radid' => $rad['id']));
			$existing_tags = array();
			foreach ($seltags as $tg) $existing_tags[$tg['tag']] = true;
			
			static $tags = null;
			if($tags == null) $tags = $this->db->prepare('INSERT INTO '.self::TAGS_TABLE .'(raadsstuk, tag) VALUES(:raadsstuk, :tag)');

			$i = 0;
			foreach ($this->tags as $tagid) {
				if(!isset($existing_tags[$tagid])) {
					if(defined('DRY_RUN')) throw new RuntimeException("Before assigning tag {$tagid} to raadsstuk: {$rad['id']}");
					$tags->execute(array(':raadsstuk' => $rad['id'], ':tag' => $tagid));
					$i += 1;
				}
			}
			$log->postUpdate($i == 0? "No new tags were added to raadsstuk: {$rad['id']}": "Successfully linked {$i} new tags with raadsstuk id: {$rad['id']}");
		
			$this->getSubmitter()->merge($rad['id']);
			$this->getVoteBlock()->merge($rad['id']);
			
			return $rad['id'];
		}
	}

	
	/** Ensure politician has valid appointment at vote date. */
	public function acceptPolitician(PoliticianModel $pol) {
		if(!$pol->hasAppointment($this->region, $this->vote_date)) {
			$log = JLogger::getLogger('utils.import.errors');
			$log->warning("The politician ({$pol->initials} {$pol->last_name} {$pol->gender}) can't vote or submit raadsstuk '{$this->title}', he/she hasn't any valid appointment at {$this->vote_date} in region {$this->region->getPath()}.\n");
			//print_r($pol->manager->ranges);
			throw new InvalidArgumentException("The politician ex:{$pol->getId()}, in:{$pol->externalId} ({$pol->last_name}) can't vote or submit this raadsstuk, he/she hasn't any valid appointment at {$this->vote_date} in region {$this->region->getPath()}.");
		}
	}
		
		
	/**
	 * Lookup for parent raadsstuk id.
	 *
	 * @throws NotFoundException if raadsstuk with such id is not yet defined
	 * @param string $extid external id
	 * @return integer internal id
	 */
	public static function mapExternalToInternalId($extid) {
		if(!isset(self::$raadsstukken[$extid])) throw new NotFoundException("Raadsstuk with external id '{$extid}' is not found!");
		return self::$raadsstukken[$extid];
	}
	
	
	/**
	 * Export raadsstukken to XML stream.
	 *
	 * WARNING: $regs will limit initial set of raadsstukken, however any dependency will be fetched on demand
	 * without taking region limit in account (to generate valid import files). Use merge mode when importing.
	 * 
	 * @param ModelSchema $schema the schema used to build the raadsstukken
	 * @param PDO $pdo DB connection
	 * @param XMLWriter $xw XML stream
	 * @param string $site site name
	 * @param ExportQueryBuilder $query limits the set of raadsstukken
	 * 
	 */
	public static function toXmlWrite($schema, $pdo, $xw, $site, $query = null) {
		if($query) $mwhere = $query->buildWhere($pdo);
		else $mwhere = '';

		$sql = <<<SQL
			SELECT r.*, st.name as subname, rt.name as radname 
		
			FROM rs_raadsstukken r
			JOIN sys_site s ON s.id = r.site_id
			JOIN rs_raadsstukken_submit_type st ON st.id = r.submitter
			JOIN rs_raadsstukken_type rt ON rt.id = r.type

			WHERE s.title = :site {$mwhere}
			
			ORDER BY r.parent DESC
SQL;

		$sel = $pdo->prepare($sql);
		
		$refetch = $pdo->prepare('SELECT r.*, st.name as subname, rt.name as radname FROM rs_raadsstukken r JOIN sys_site s ON s.id = r.site_id JOIN rs_raadsstukken_submit_type st ON st.id = r.submitter JOIN rs_raadsstukken_type rt ON rt.id = r.type WHERE r.id = :radid');
		$submitters = $pdo->prepare('SELECT sb.politician FROM rs_raadsstukken_submitters sb WHERE sb.raadsstuk = :radid');
		$tags = $pdo->prepare('SELECT t.name FROM rs_raadsstukken_tags rt JOIN sys_tags t ON t.id = rt.tag WHERE rt.raadsstuk = :radid');
		$categories = $pdo->prepare('SELECT c.id, c.name FROM rs_raadsstukken_categories rc JOIN sys_categories c ON c.id = rc.category WHERE rc.raadsstuk = :radid');
		$vote_party_check = $pdo->prepare('SELECT SUM(CASE WHEN p.def_party IS NOT NULL THEN 1 ELSE 0 END) as party_bound, SUM(CASE WHEN p.def_party IS NULL THEN 1 ELSE 0 END) as pol_bound FROM rs_votes v JOIN pol_politicians p ON p.id = v.politician WHERE v.raadsstuk = :radid');
		$votes = $pdo->prepare('SELECT * FROM rs_votes v WHERE v.raadsstuk = :radid');
		
		$data[':site'] = $site;
		if(!$sel->execute($data)) throw new RuntimeException("Can't execute select query. Database error.");
		$xw->startElement('raadsstukken'); //<raadsstukken>
		
		$ids = array();
		$circular = array();
		$regionschema = $schema->getRegionSchema();
		$partyschema = $schema->getPartySchema();
		$polschema = $schema->getPoliticianSchema();
		$categoryschema = $schema->getCategorySchema();
		while(($row = $sel->fetch(PDO::FETCH_ASSOC))) {
			if(!isset($ids[$row['id']])) {
				self::writeWithDependency($refetch, $submitters, $tags, $categories, $vote_party_check, $votes, $regionschema, $partyschema, $polschema, $categoryschema, $xw, $row, $ids, $circular);
			}
    	}
    	
    	$xw->endElement(); //</raadsstukken>
	}
	
	/** Resolves raadsstuk dependency. */
	private static function writeWithDependency($refetch, $submitters, $tags, $categories, $vote_party_check, $votes, $regionschema, $partyschema, $polschema, $categoryschema, $xw, $row, &$ids, &$circular) {
		if($row['parent'] && !isset($ids[$row['parent']])) { //dependency
			$circular[$row['id']] = true;
			if(isset($circular[$row['parent']])) {
				throw new RuntimeException('Circular dependence detected for raadsstuks: '.implode(', ', array_keys($circular)));
			}
			$refetch->execute(array(':radid' => $row['parent']));
			$parent_row = $refetch->fetch(PDO::FETCH_ASSOC);
			
			self::writeWithDependency($refetch, $submitters, $tags, $categories, $vote_party_check, $votes, $regionschema, $partyschema, $polschema, $categoryschema, $xw, $parent_row, $ids, $circular);
			
			unset($circular[$row['id']]);
		}
		
		$ids[$row['id']] = true;
		self::writeRaadsstuk($submitters, $tags, $categories, $vote_party_check, $votes, $xw, $row, $regionschema, $partyschema, $polschema, $categoryschema);
	}
	
	/** Serialize raadsstuk to XML stream. */
	private static function writeRaadsstuk($submitters, $tags, $categories, $vote_party_check, $votes, $xw, $row, $regionschema, $partyschema, $polschema, $categoryschema) {
		$reg = $regionschema->lookup($row['region']);
		$votedate = reset(explode(' ', $row['vote_date']));
		
		$xw->startElement('raadsstuk'); //<raadsstuk>
		$xw->writeAttribute('id', $row['id']);
		$xw->writeAttribute('title', $row['title']);
		$xw->writeAttribute('code', $row['code']);
		$xw->writeAttribute('vote_date', $votedate);
		$xw->writeAttribute('region', $reg->getPath());
		$xw->writeAttribute('show', $row['show']);
		
		$xw->writeElement('summary', $row['summary']); //<summary />
			
		$xw->startElement('submitter'); //<submitter>
		$xw->writeAttribute('type', $row['radname']);
		$xw->writeAttribute('submitter', $row['subname']);
			
		if(!empty($row['parent'])) {
			$xw->startElement('parentref'); //<parentref>
			$xw->writeAttribute('raadsstuk', $row['parent']);
			$xw->endElement(); //</parentref>
		}
		
		//OK, this sucks, but requires less memory thant prefetched indexed whole table
		//this script doesn't need to run quickly, the memory limits are more real.
		if(!$submitters->execute(array(':radid' => $row['id']))) throw new RuntimeException("Can't execute select query. Database error.");
		while(($sub = $submitters->fetch(PDO::FETCH_ASSOC))) {
			$xw->startElement('politicianref'); //<politicianref>
			
			$pol = $polschema->lookup($sub['politician']);
			$pol->touchAppointment($reg, $votedate);
			
			$xw->writeAttribute('politician', $pol->getId());
			
			$xw->endElement(); //</politicianref>
		}
		
		$xw->endElement(); //</submitter>
  
		//tags
		if(!$tags->execute(array(':radid' => $row['id']))) throw new RuntimeException("Can't execute select query. Database error.");
		while(($tag = $tags->fetch(PDO::FETCH_ASSOC))) {
			$xw->startElement('tag'); //<tag>
			$xw->writeAttribute('name', $tag['name']);
			$xw->endElement(); //</tag>
		}
		
		//categories
		if(!$categories->execute(array(':radid' => $row['id']))) throw new RuntimeException("Can't execute select query. Database error.");
		while(($category = $categories->fetch(PDO::FETCH_ASSOC))) {
			$categoryschema->lookup($category['id'])->touch();
			$xw->startElement('category'); //<category>
			$xw->writeAttribute('name', $category['name']);
			$xw->endElement(); //</category>
		}
		
		//votes
		if(!$vote_party_check->execute(array(':radid' => $row['id']))) throw new RuntimeException("Can't execute select query. Database error.");
		$counts = $vote_party_check->fetch(PDO::FETCH_ASSOC);
		if($counts['party_bound'] > 0 && $counts['pol_bound'] > 0) throw new RuntimeException("Database is inconsistent. Raadsstuk {$row['id']} has {$counts['party_bound']} votes bound to parties (default politicians) and {$counts['pol_bound']} votes bound to politicians. This is not allowed.");
		
		$xw->startElement('votes'); //<votes>
		$xw->writeAttribute('type', $counts['party_bound'] > 0? 'party': 'politician');
		
		//yes, it is possible to set result as 'not-voted' and provide votes
		//I don't know if this is a feature or a bug of the backoffice, but we stay compatible.
		$xw->writeAttribute('result', $row['result'] == 0? 'new': ($row['result'] == 1? 'accepted': 'declined'));
		
		//fetch votes
		if(!$votes->execute(array(':radid' => $row['id']))) throw new RuntimeException("Can't execute select query. Database error.");
		while(($vt = $votes->fetch(PDO::FETCH_ASSOC))) {
			$par = $partyschema->lookup($vt['party']);
			$par->touch();
			$par->touchInRegion($row['region']);
			
			$xw->startElement('vote'); //<vote>
			$xw->writeAttribute('vote', $vt['vote'] == 0? 'yes': ($vt['vote'] == 1? 'no': ($vt['vote'] == 2? 'remember': 'absent')));
			$xw->writeAttribute('party', $par->getName());
			
			if($counts['party_bound'] == 0) {
				$pol = $polschema->lookup($vt['politician']);
				
				$pol->touchAppointment($reg, $votedate);
				$xw->writeAttribute('politician', $pol->getId());
			}
			$xw->endElement(); //</vote>
		}
		
		$xw->endElement(); //</votes>

		$xw->endElement(); //</raadsstuk>
	}
}


/**
* Vote procedure.
* @author Sardar Yumatov (ja.doma@gmail.com)
*/
abstract class RaadsstukVoteBlock {
	const TABLE_NAME = 'rs_votes';
	private static $ins = null;
	private static $update = null;
	private static $del = null;

	const YES = 0;
	const NO = 1;
	const REMEMBER = 2;
	const ABSENT = 3;

	const RESULT_NEW = 0;
	const RESULT_ACCEPTED = 1;
	const RESULT_DECLINED = 2;

	protected $type;
	protected $schema;
	protected $db;
	protected $raadsstuk;
	protected $votes = array();
	protected $result = self::RESULT_NEW;


	/** Construct new vote block */
	public function __construct($raadsstuk, $schema, $db, $type, $result = null) {
		$this->raadsstuk = $raadsstuk;
		$this->schema = $schema;
		$this->db = $db;
		$this->type = $type;

		if(self::$ins == null) {
			self::$ins = $this->db->prepare('INSERT INTO '.self::TABLE_NAME.'(politician, raadsstuk, vote) VALUES(:politician, :raadsstuk, :vote);');
			if(!self::$ins) throw new RuntimeException("Failed to create prepared statement for insert!");
			
			self::$update = $this->db->prepare('UPDATE '.self::TABLE_NAME.' SET vote = :vote WHERE id = :id');
			if(!self::$update) throw new RuntimeException("Failed to create prepared statement for update!");
			
			self::$del = $this->db->prepare('DELETE FROM '.self::TABLE_NAME.' WHERE raadsstuk = :raadsstuk');
			if(!self::$del) throw new RuntimeException("Failed to create prepared statement for delete!");
		}

		if($result !== null) {
			static $types = array(
				'new' => self::RESULT_NEW,
				'declined' => self::RESULT_DECLINED,
				'accepted' => self::RESULT_ACCEPTED
			);

			if(!ctype_digit((string)$result)) {
				$result = strtolower(trim($result));
				if(!isset($types[$result])) throw new InvalidArgumentException("Unknown result type: '{$result}', expecting one of constants!");
				else $result = $types[$result];
			} elseif(!in_array(intval($result), array(self::RESULT_NEW, self::RESULT_ACCEPTED, self::RESULT_DECLINED))) {
				throw new InvalidArgumentException("Unknown result type: {$result}, expecting one of constants [0..2].");
			}

			$this->result = $result;
		}
	}

	/** Returns new vote block for type. */
	public static function forType($raadsstuk, $schema, $db, $type, $result = null) {
		$type = trim(strtolower($type));
		switch ($type) {
			case 'party': return new PartyRaadsstukVoteBlock($raadsstuk, $schema, $db, $type, $result);
			case 'politician': return new PoliticianRaadsstukVoteBlock($raadsstuk, $schema, $db, $type, $result);

			default: throw new InvalidArgumentException("Unknown vote block type: {$type}");
		}
	}

	/** Returns type of this vote block */
	public function getType() {
		return $this->type;
	}

	/**
	 * Returns voting result.
	 * @return integer one of result constants
	 */
	public function getResult() {
		return $this->result;
	}

	/**
	 * Vote by politician.
	 * @throws RuntimeException if voting by politician is not allowed by this block
	 * @param integer $vote one of vote constants
	 * @param string $politician_id external id to politician
	 * @param string $party optional party name
	 * @return void
	 */
	public function votePolitician($vote, $politician_id, $party) {
		throw new RuntimeException("Voting by politician is not allowed by this vote block type: {$this->type}! Vote: {$vote}, politician: {$politician_id}");
	}

	/**
	 * Vote by party.
	 * @throws RuntimeException if voting by party is not allowed by this block
	 * @param integer $vote one of vote is constants
	 * @param string $party_name party name
	 * @return void
	 */
	public function voteParty($vote, $party_name) {
		throw new RuntimeException("Voting by party is not allowed by this vote block type: {$this->type}! Vote: {$vote}, party: {$party_name}");
	}

	/**
	 * Assign votes to the given raadsstuk.
	 * @throws RuntimeException on any error
	 * @param integer $raaadstukid raadsstuk id
	 * @return void
	 */
	public function install($raaadstukid, $overwrite = false) {
		$log = JLogger::getLogger("utils.import.raadsstuk");

		$sel = $this->db->prepare('SELECT region FROM rs_raadsstukken WHERE id = :radid');
		if(!$sel->execute(array(':radid' => $raaadstukid))) throw new RuntimeException("Can't fetch region, database error.");
		$q = $sel->fetch(PDO::FETCH_ASSOC);
		if (defined('REGION_ACCESS') && !isset($GLOBALS['REGION_ACCESS_LIST'][$q['region']]))
			throw new RuntimeException('Not allowed to insert votes for document: '.$raaadstukid);

		if(defined('DRY_RUN')) throw new RuntimeException("Before voting for the raadsstuk: ".$raaadstukid);
		
		if($overwrite) {
			$log->preUpdate("Clearing old votes of raadsstuk: {$raaadstukid}");
			self::$del->execute(array(
				':raadsstuk' => $raaadstukid
			));
			$log->postUpdate("Successfully cleared all votes of raadstuk: {$raaadstukid}");
		}
		
		$log->preUpdate("Making ".sizeof($this->votes)." votes for raadsstuk: {$raaadstukid}");
		foreach ($this->votes as $polid => $vote) {
			self::$ins->execute(array(
				':politician' => $polid,
				':raadsstuk' => $raaadstukid,
				':vote' => $vote
			));

			if(self::$ins->rowCount() != 1) throw new RuntimeException("Can't set vote for raadsstuk id: '{$raaadstukid}' -- [vote: {$vote}, politician id: {$polid}]");
		}

		$log->postUpdate("Successfully inserted all votes.");
	}
	

	/**
	 * Merge votes with given raadsstuk.
	 * @throws RuntimeException on any error
	 * @param integer $raaadstukid raadsstuk id
	 * @return void
	 */
	public function merge($raadsstukid) {
		$log = JLogger::getLogger("utils.import.raadsstuk");
		$log->preUpdate("Merging ".sizeof($this->votes)." votes with raadsstuk: {$raadsstukid}");

		$sel = $this->db->prepare('SELECT region FROM rs_raadsstukken WHERE id = :radid');
		if(!$sel->execute(array(':radid' => $raadsstukid))) throw new RuntimeException("Can't fetch region, database error.");
		$q = $sel->fetch(PDO::FETCH_ASSOC);
		if (defined('REGION_ACCESS') && !isset($GLOBALS['REGION_ACCESS_LIST'][$q['region']]))
			throw new RuntimeException('Not allowed to merge votes for document: '.$raadsstukid);

		static $sel = null;
		if($sel == null) $sel = $this->db->prepare('SELECT id, politician, vote FROM rs_votes WHERE raadsstuk = :radid');
		if(!$sel->execute(array(':radid' => $raadsstukid))) throw new RuntimeException("Can't fetch all votes, database error.");
		
		$existing_votes = array();
		foreach ($sel as $vote) $existing_votes[$vote['politician']] = $vote;
		
		$i = 0;
		foreach ($this->votes as $polid => $vote) {
			if(!isset($existing_votes[$polid]) || $existing_votes[$polid]['vote'] != $vote) {
				if(defined('DRY_RUN')) throw new RuntimeException("Before ".(isset($existing_votes[$polid])? 'updating existing': 'inserting new')." vote of politician {$polid} for the raadsstuk: ".$raadsstukid);
				
				if(!isset($existing_votes[$polid])) { //MySQL replace will be better here...
					self::$ins->execute(array(
						':politician' => $polid,
						':raadsstuk' => $raadsstukid,
						':vote' => $vote
					));
					if(self::$ins->rowCount() != 1) throw new RuntimeException("Can't set vote for raadsstuk id: '{$raadsstukid}' -- [vote: {$vote}, politician id: {$polid}]");
				} else {
					self::$update->execute(array(
						':id' => $existing_votes[$polid]['id'],
						':vote' => $vote
					));
					if(self::$update->rowCount() != 1) throw new RuntimeException("Can't update vote for raadsstuk id: '{$raadsstukid}' -- [vote: {$vote}, politician id: {$polid}]");
				}
				
				$i += 1;
			}
		}

		$log->postUpdate($i == 0? "No new votes where added or updated": "Successfully merged {$i} new/updated votes from: ".sizeof($this->votes).' total');
	}

	
	/** Recognize vote */
	public static function mapVote($vote) {
		static $votes = array(
			'yes' => self::YES,
			'no' =>  self::NO,
			'remember' => self::REMEMBER,
			'absent' =>  self::ABSENT
		);

		if(!ctype_digit((string)$vote)) {
			$vote = strtolower(trim((string)$vote));
			if(isset($votes[$vote])) return $votes[$vote];
			else throw new RuntimeException("Unknown vote '{$vote}', excepting on of the constants!");
		}

		if(!in_array(intval($vote), array(self::YES, self::NO, self::REMEMBER, self::ABSENT)))
			throw new InvalidArgumentException("Unknown vote: '{$vote}', must be one of the constants.");

		return intval($vote);
	}
}


/**
* Vote procedure with resolution up to party.
* @author Sardar Yumatov (ja.doma@gmail.com)
*/
class PartyRaadsstukVoteBlock extends RaadsstukVoteBlock {

	/**
	 * Vote by party.
	 * @param integer $vote one of vote is constants
	 * @param string $party_name party name
	 * @return void
	 */
	public function voteParty($vote, $party_name) {
		$vote = self::mapVote($vote);

		$par = $this->schema->getPartySchema();
		$pol = $this->schema->getPoliticianSchema();

		$time = strtotime($this->raadsstuk->voteDate);
		$before = date('Y-m-d', strtotime("-1 day", $time));
		$after = date('Y-m-d', strtotime("+1 day", $time));
		
		$id = $pol->getDefaultPoliticianForParty($par->getParty($party_name), $this->raadsstuk->region, $before, $after)->getId();

		$this->votes[$id] = $vote;
	}
}


/**
* Vote procedure with resolution up to politician
*
* @author Sardar Yumatov (ja.doma@gmail.com)
*/
class PoliticianRaadsstukVoteBlock extends RaadsstukVoteBlock {

	/**
	 * Vote by politician.
	 * @throws RuntimeException if voting by politician is not allowed by this block
	 * @param integer $vote one of vote constants
	 * @param string $politician_id external id to politician
	 * @param string $party optional party name
	 * @return void
	 */
	public function votePolitician($vote, $politician_id, $party = null) {
		$vote = self::mapVote($vote);

		$par = $this->schema->getPoliticianSchema();
		$pol = $par->getPolitician($politician_id);
		
		$par = $this->schema->getPartySchema();
		if($party != null) { //auto register appointment
			$party = $par->getParty($party);
			$time = strtotime($this->raadsstuk->voteDate);
			$before = date('Y-m-d', strtotime("-1 day", $time));
			$after = date('Y-m-d', strtotime("+1 day", $time));
			
			$pol->registerFunction($this->raadsstuk->region, $party, $before, $after);
		} else $this->raadsstuk->acceptPolitician($pol); //throws exception

		$id = $pol->getId();
		$this->votes[$id] = $vote;
	}
}




/**
* Submitting procedure.
* @author Sardar Yumatov (ja.doma@gmail.com)
*/
abstract class SubmitterProcedure {

	const SUBMITTERS_TABLE = 'rs_raadsstukken_submitters';


	protected $schema;
	protected $db;
	protected $raadsstuk;

	protected $submitter;
	protected $type;
	protected $parent = null;
	protected $politicians = array();

	private static $types = null;
	private static $submitters = null;



	/**
	 * Construct new submitter.
	 * This constructor will lookup for $type en $submitter.
	 *
	 * @param ModelSchema $schema
	 * @param PDO $db
	 * @param string $type valid/real type name
	 * @param string $submitter valid/real submitter type
	 */
	public function __construct($raadsstuk, $schema, $db, $type, $submitter) {
		$this->raadsstuk = $raadsstuk;
		$this->schema = $schema;
		$this->db = $db;

		if(self::$types == null) {
			self::$types = array();

			$sel = $this->db->query('SELECT * FROM rs_raadsstukken_type;');
			foreach ($sel as $row) {
				self::$types[strtolower(trim($row['name']))] = $row['id'];
			}
			unset($sel);

			$sel = $this->db->query('SELECT * FROM rs_raadsstukken_submit_type;');
			foreach ($sel as $row) {
				self::$submitters[strtolower(trim($row['name']))] = $row['id'];
			}
			unset($sel);
		}

		$type = strtolower(trim($type));
		$submitter = strtolower(trim($submitter));
		
		if(!isset(self::$types[$type])) throw new InvalidArgumentException("Unknown submitter type: '{$type}'");
		$this->type = self::$types[$type];

		if(!isset(self::$submitters[$submitter])) throw new InvalidArgumentException("Unknown submitter: '{$submitter}'");
		$this->submitter = self::$submitters[$submitter];
	}
	
	/**
	 * Return procedure for type.
	 *
	 * @param ModelSchema $schema
	 * @param PDO $db
	 * @param string $type
	 * @param string $submitter
	 * @return SubmitterProcedure
	 */
	public static function forType($raadsstuk, $schema, $db, $type, $submitter = null) {
		switch (strtolower(trim($type))) {
			case 'raadsvoorstel': return new RaadsvoorstelSubmitterProcedure($raadsstuk, $schema, $db, $type, $submitter);
			case 'burgerinitiatief': return new BurgerinitiatiefSubmitterProcedure($raadsstuk, $schema, $db, $type, $submitter);
			case 'initiatiefvoorstel': return new InitiatiefvoorstelSubmitterProcedure($raadsstuk, $schema, $db, $type, $submitter);
			case 'motie':
			case 'amendement': return new AmendementSubmitterProcedure($raadsstuk, $schema, $db, $type, $submitter);
			case 'onbekend': return new OnbekendSubmitterProcedure($raadsstuk, $schema, $db, $type, $submitter);

			default:
				throw new InvalidArgumentException("Unknown submitter type: {$type}");
		}
	}


	/**
	 * Returns reference (id) of the raadsstuk type object.
	 * @return integer
	 */
	public function getTypeRef() {
		return $this->type;
	}

	/**
	 * Returns reference (id) of the submitter object.
	 * @return integer
	 */
	public function getSubmitterRef() {
		return $this->submitter;
	}

	/**
	 * Returns parent raadsstuk id.
	 * @return integer null if there is not parent defined
	 */
	public function getParentRaadstukId() {
		return $this->parent;
	}

	/**
	 * Assign parent reference.
	 *
	 * @throws RuntimeException if violates logic constraint
	 * @param string $parent_id parent raadsstuk external id
	 * @return void
	 */
	public function setParentRaadsstuk($parent_id) {
		throw new RuntimeException("Parent is not used by this procedure! Parent: {$parent_id}");
	}

	/**
	 * Assign politician as submitter.
	 *
	 * @throws RuntimeException if violates logic constraint
	 * @param string $politician_id politician external id
	 * @return void
	 */
	public function addPolitician($politician_id) {
		throw new RuntimeException("Politician submitter is not used by this procedure! Politician: {$politician_id}");
	}


	/**
	 * Install submitters if needed
	 * @param integer $raadsstukid target raadsstuk
	 * @return void
	 */
	public function install($raadsstukid, $overwrite = false) {
		if($this->politicians) {
			$log = JLogger::getLogger("utils.import.raadsstuk");

			$sel = $this->db->prepare('SELECT region FROM rs_raadsstukken WHERE id = :radid');
			if(!$sel->execute(array(':radid' => $raadsstukid))) throw new RuntimeException("Can't fetch region, database error.");
			$q = $sel->fetch(PDO::FETCH_ASSOC);
			if (defined('REGION_ACCESS') && !isset($GLOBALS['REGION_ACCESS_LIST'][$q['region']]))
				throw new RuntimeException('Not allowed to insert submitters for document: '.$raadsstukid);

			
			static $del = null;
			$del = $this->db->prepare('DELETE FROM '.self::SUBMITTERS_TABLE.' WHERE raadsstuk = :raadsstuk');
			if($overwrite) {
				$log->preUpdate("Clearing all submitter politicians of raadsstuk: {$raadsstukid}");
				$del->execute(array(':raadsstuk' => $raadsstukid));
				$log->postUpdate("Successfully cleared all submitter politicians of raadsstuk: {$raadsstukid}");
			}

			$log->preUpdate("Inserting submitter politicians for raadsstuk: {$raadsstukid}");

			static $ins = null;
			$ins = $this->db->prepare('INSERT INTO '.self::SUBMITTERS_TABLE.'(raadsstuk, politician) VALUES(:raadsstuk, :politician);');

			if(defined('DRY_RUN')) throw new RuntimeException("Before setting submitters of the raadsstuk: ".$raadsstukid);

			foreach (array_keys($this->politicians) as $pol) {
				$ins->execute(array(
					':raadsstuk' => $raadsstukid,
					':politician' => $pol
				));
			}

			$log->postUpdate("Successfully inserted all submitter politicians of raadsstuk: {$raadsstukid}");
		}
	}
	
	
	/**
	 * Merge submitters.
	 * @param integer $raadsstukid target raadsstuk
	 * @return void
	 */
	public function merge($raadsstukid) {
		if($this->politicians) {
			$log = JLogger::getLogger("utils.import.raadsstuk");

			$log->preUpdate("Merging submitter politicians for raadsstuk: {$raadsstukid}");
			
			$s = $this->db->prepare('SELECT region FROM rs_raadsstukken WHERE id = :radid');
			if(!$s->execute(array(':radid' => $raadsstukid))) throw new RuntimeException("Can't fetch region, database error.");
			$q = $s->fetch(PDO::FETCH_ASSOC);
			if (defined('REGION_ACCESS') && !isset($GLOBALS['REGION_ACCESS_LIST'][$q['region']]))
				throw new RuntimeException('Not allowed to merge submitters for document: '.$raadsstukid);

			static $sel = null;
			if($sel == null) $sel = $this->db->prepare('SELECT * FROM '.self::SUBMITTERS_TABLE.' WHERE raadsstuk = :raadsstuk');
			if(!$sel->execute(array(':raadsstuk' => $raadsstukid))) throw new RuntimeException("Can't fetch existing submitters for raadsstuk {$raadsstukid}, database error.");
			
			$existing_submitters = array();
			foreach ($sel as $rad) $existing_submitters[$rad['politician']] = $rad['id'];

			static $ins = null;
			if($ins == null) $ins = $this->db->prepare('INSERT INTO '.self::SUBMITTERS_TABLE.'(raadsstuk, politician) VALUES(:raadsstuk, :politician);');

			$i = 0;
			foreach (array_keys($this->politicians) as $pol) {
				if(!isset($existing_submitters[$pol])) {
					if(defined('DRY_RUN')) throw new RuntimeException("Before setting submitter politician {$pol} for the raadsstuk: ".$raadsstukid);
					
					$ins->execute(array(
						':raadsstuk' => $raadsstukid,
						':politician' => $pol
					));
					
					$i += 1;
				}
			}

			$log->postUpdate($i == 0? "No new submitters were added to raadsstuk {$raadsstukid}": "Successfully inserted {$i} new submitter politicians for raadsstuk: {$raadsstukid}");
		}
	}
}


/**
* Raadsvoorstel.
* @author Sardar Yumatov (ja.doma@gmail.com)
*/
class RaadsvoorstelSubmitterProcedure extends SubmitterProcedure {

	const DEFAULT_SUBMITTER = 'College';

	/**
	 * Allows 'College' and 'Presidium' submitters.
	 *
	 * @param ModelSchema $schema
	 * @param PDO $db
	 * @param string $type
	 * @param string $submitter
	 */
	public function __construct($raadsstuk, $schema, $db, $type, $submitter = null) {
		if($submitter) {
			$submitter_ = strtolower(trim($submitter));
			if(in_array($submitter_, array('college', 'presidium', 'onbekend'))) $submitter = ucfirst($submitter_);
			else throw new InvalidArgumentException("Expecting submitter 'College', 'Presidium' or 'Onbekend', but got: {$submitter}");
		} else $submitter = self::DEFAULT_SUBMITTER;

		parent::__construct($raadsstuk, $schema, $db, 'Raadsvoorstel', $submitter);
	}
}


/**
* Burgerinitiatief
* @author Sardar Yumatov (ja.doma@gmail.com)
*/
class BurgerinitiatiefSubmitterProcedure extends SubmitterProcedure {

	public function __construct($raadsstuk, $schema, $db, $type, $submitter = null) {
		parent::__construct($raadsstuk, $schema, $db, 'Burgerinitiatief', 'burger');
	}
}

/**
* Initiatiefvoorstel
* @author Sardar Yumatov (ja.doma@gmail.com)
*/
class InitiatiefvoorstelSubmitterProcedure extends SubmitterProcedure {

	public function __construct($raadsstuk, $schema, $db, $type = null, $submitter = null) {
		parent::__construct($raadsstuk, $schema, $db, 'Initiatiefvoorstel', 'raadslid');
	}

	/**
	 * Assign politician as submitter.
	 *
	 * @param string $politician_id politician external id
	 * @return void
	 */
	public function addPolitician($politician_id) {
		$pol = $this->schema->getPoliticianSchema()->getPolitician($politician_id);
		$this->raadsstuk->acceptPolitician($pol); //throws exception
		$this->politicians[$pol->getId()] = true;
	}
}


/**
* Amendement.
* @author Sardar Yumatov (ja.doma@gmail.com)
*/
class AmendementSubmitterProcedure extends SubmitterProcedure {

	public function __construct($raadsstuk, $schema, $db, $type, $submitter = null) {
		parent::__construct($raadsstuk, $schema, $db, 'Raadsvoorstel', 'Raadslid');
	}

	/**
	 * Returns parent raadsstuk id.
	 * @return integer null if there is not parent defined
	 */
	public function getParentRaadstukId() {
		return $this->parent;
	}

	/**
	 * Assign parent reference.
	 * @throws RuntimeException if parent raadstuk is not found
	 * @param string $parent_id parent raadsstuk external id
	 * @return void
	 */
	public function setParentRaadsstuk($parent_id) {
		$this->parent = RaadsstukModel::mapExternalToInternalId($parent_id);
	}

	/**
	 * Assign politician as submitter.
	 *
	 * @throws RuntimeException if politician is not found
	 * @param string $politician_id politician external id
	 * @return void
	 */
	public function addPolitician($politician_id) {
		$pol = $this->schema->getPoliticianSchema()->getPolitician($politician_id);
		$this->raadsstuk->acceptPolitician($pol); //throws exception
		$this->politicians[$pol->getId()] = true;
	}
}

/**
* Onbekend.
* @author Sardar Yumatov (ja.doma@gmail.com)
*/
class OnbekendSubmitterProcedure extends SubmitterProcedure {

	public function __construct($raadsstuk, $schema, $db, $type = null, $submitter = null) {
		parent::__construct($raadsstuk, $schema, $db, 'Onbekend', 'Onbekend');
	}
}

?>