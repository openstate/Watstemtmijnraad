<?php

require_once('CategoryModel.class.php');
require_once('NotFoundException.class.php');
require_once('JLogger.class.php');


/**
* Handles category set.
*
* @author Sardar Yumatov (ja.doma@gmail.com)
*/
class CategoryModelSchema {

	/** Id of the category object that represents "No category" ("Geen") */
	const NO_CATEGORY_OBJECT = -1;
	/** Table containing all the levels. */
	const LEVELS_TABLE = 'sys_levels';

	
	/** category index by stemmed name */
	private $categories = array();
	/** category index by id */
	private $id_index = array();
	/** Level name to number mapping. */
	private $level_name = array();
	/** Level id to level id. used for level checks (probably never needed,
	 * but levels are stored in DB, so sparse level range is theoretically possible.) */
	private $level_id = array();

	/** @var PDO */
	private $db;
	private $global_schema;

	private $trace = false;
	private $traced = array();
	
	/**
	 * Load category schema.
	 *
	 * @throws RuntimeException on any error
	 * @param PDO $db database access
	 * @param ModelSchema $global_schema the global schema
	 */
	public function __construct($db, ModelSchema $global_schema) {
		$log = JLogger::getLogger('utils.import.schema.category');

		$this->db = $db;
		$this->global_schema = $global_schema;

		$log->enter("Starting with fetch of the whole category schema.");

		$log->preSelect("Fetching all levels.");
		$syslevs = $this->db->query('SELECT * FROM '.self::LEVELS_TABLE.';');
		$syslevs->setFetchMode(PDO::FETCH_ASSOC);
		foreach ($syslevs as $lv) {
			$this->level_name[ModelSchema::normalize($lv['name'])] = $lv['id'];
			$this->level_id[$lv['id']] = $lv['id'];
		}
		
		$log->preSelect("Fetching all category level registrations.");
		$levs = $this->db->query('SELECT * FROM '.CategoryModel::LEVEL_CAT_TABLE.';');
  		$levs->setFetchMode(PDO::FETCH_ASSOC);

		$levdescr = array();
		foreach ($levs as $lv) $levdescr[$lv['category']][$lv['level']] = $lv['description'];

		$log->preSelect("Fetching all categories");
		$ret = $this->db->query('SELECT * FROM '.CategoryModel::TABLE_NAME);
		$ret->setFetchMode(PDO::FETCH_ASSOC);


  		foreach ($ret as $row) {
			$cat = new CategoryModel($row['name'], $row['description']);

			$lvs = isset($levdescr[$row['id']])? $levdescr[$row['id']]: array();
  			$cat->resolve($this, $this->db, $row['id'], $lvs);
			$this->id_index[$cat->getId()] = $cat;
			$this->categories[$cat->getKey()] = $cat;
  		}

  		$log->leave("Successfully ended fetching category schema.");
	}

	/** Start tracing all queries to this schema. */
	public function startDependencyTrace() {
		$this->trace = true;
	}
	
	public function trace($regid) {
		if($this->trace) $this->traced[$regid] = true;
	}
	
	public function isTraced($regid) {
		return !$this->trace || isset($this->traced[$regid]);
	}

	/**
	 * Add/resolve category
	 *
	 * @param CategoryModel $cat
	 * @return CategoryModel either new or already defined category
	 */
	public function addCategory(CategoryModel $cat) {
		if(isset($this->categories[$cat->getKey()])) return $this->categories[$cat->getKey()];

		$log = JLogger::getLogger('utils.import.schema.category');
		$log->debug("Creating new category: {$cat->getName()}");

		$cat->resolve($this, $this->db, null, array());

		$this->id_index[$cat->getId()] = $cat;
		$this->categories[$cat->getKey()] = $cat;
		if($this->trace) $this->traced[$cat->getId()] = true;
		return $cat;
	}


	/**
	 * Returns category by name.
	 * Method creates category without description on demand.
	 *
	 * @param string $name name of the category
	 * @return CategoryModel
	 */
	public function getCategory($name) {
		$key = CategoryModel::stem($name);

		if(!isset($this->categories[$key])) {
			$log = JLogger::getLogger('utils.import.schema.category');
			$log->debug("Creating new category: {$name}");

			$c = new CategoryModel($name, null);
			$c->resolve($this, $this->db, null, array());
			$this->id_index[$c->getId()] = $c;
			if($this->trace) $this->traced[$c->getId()] = true;
			$this->categories[$key] = $c;
		}

		if($this->trace) $this->traced[$this->categories[$key]->getId()] = true;
		return $this->categories[$key];
	}

	/**
	 * Returns category by name, registered in given $level.
	 * Method creates category without description on demand and registers it in given region level.
	 *
	 * @param string $name name of the category
	 * @param integer $level the level [1..5]
	 * @return CategoryModel
	 */
	public function getCategoryInLevel($name, $level) {
		$cat = $this->getCategory($name);
		$cat->ensureLevelRegistered($level);
		return $cat;
	}

	/**
	 * Returns category by $name registered in given $level.
	 * Method throws exception if category isn't found or is not registered in requested $level.
	 *
	 * @throws NotFoundException if category isn't found or is not registered in requested $level
	 * @param string $name category name
	 * @param integer $level region level
	 * @return CategoryModel
	 */
	public function pickCategoryInLevel($name, $level) {
		$key = CategoryModel::stem($name);
		
		if(isset($this->categories[$key])) {
			$cat = $this->categories[$key];
			$this->trace($cat->getId());
			if($cat->isInLevel($level)) return $cat;
		}
		
		throw new NotFoundException("The category: '{$name}' is not found in level: {$level}");
	}
	
	/**
	 * Returns category by id.
	 *
	 * @throws NotFoundException if category with such id is not foun
	 * @param integer $id category id
	 * @return CategoryModel
	 */
	public function lookup($id) {
		$this->trace($id);
		
		if(!isset($this->id_index[$id])) {
			$log = JLogger::getLogger('utils.import.schema.category');
			$log->enter("Database lookup for category id: {$id}");
			$log->preSelect("Fetching category by id: {$id}");

			$sel = $this->db->prepare('SELECT * FROM '.CategoryModel::TABLE_NAME.' WHERE id = :id');
			$row = $sel->execute(array(':id' => $id))->fetch(PDO::FETCH_ASSOC);

			if(!$row) throw new NotFoundException("Can't find category by id: {$id}");

			$log->debug("Category with id '{$id}' is found as '{$row['name']}'.");
			$log->preSelect("Fetching all category dependences for: {$id} - '{$row['name']}'");
			$levs = $this->db->prepare('SELECT * FROM '.CategoryModel::LEVEL_CAT_TABLE.' WHERE category = :category;');
  			$levs->setFetchMode(PDO::FETCH_ASSOC);
  			$levs->execute(array(':category' => $id));

			$levdescr = array();
			foreach ($levs as $lv) $levdescr[$lv['level']] = $lv['description'];

			$ret = new CategoryModel($row['name'], $row['description']);
			$ret->resolve($this, $this->db, $row['id'], $levdescr);

			$this->id_index[$ret->getId()] = $ret;
			$this->categories[$ret->getKey()] = $ret;

			$log->leave("Successfully recovered category '{$ret->getName()}' by id: {$id}");

			return $ret;
		}

		return $this->id_index[$id];
	}


	/**
	 * Serialize this category schema to the DOM tree.
	 *
	 * @param DOMDocument $dom the owner document, used to create elements
	 * @param DOMElement $root where to 'categories' element will be added
	 * @param array $options extra options
	 * @return void
	 */
	public function toXml($dom, $root, $options = null) {
		$el = $dom->createElement('categories');
		$root->appendChild($el);

		foreach ($this->categories as $cat) {
			$cat->toXml($dom, $el, $options);
		}
	}
	
	/**
	 * Serialize this schema to the XML stream
	 *
	 * @param XMLWriter $xw XML output stream
	 * @param array $options extra options
	 * @return void
	 */
	public function toXmlWrite($xw, $options = null) {
		$xw->startElement('categories'); // <categories>
		foreach ($this->categories as $cat) {
			$cat->toXmlWrite($xw, $options);
		}
		$xw->endElement(); // </categories>
	}


	/**
	 * Read & update schema from XML data.
	 *
	 * @throws RuntimeException on any error
	 * @param SimpleXMLElement $node schema node
	 * @return void
	 */
	public function update(SimpleXMLElement $node) {
		$log = JLogger::getLogger('utils.import.schema.category');
		$log->enter("Updating cateogry schema from XML source.");

		foreach ($node ->category as $cat) {
			$c = new CategoryModel((string)$cat['name'], (string)$cat['description']);
			$c = $this->addCategory($c);

			foreach ($cat->inlevel as $lev) {
				$c->registerLevelDescription($this->normalizeLevel($lev['level']), (string)$lev['description']);
			}
		}

		$log->leave("Successfully updated category schema from XML source.");
	}
	
	/**
	 * Converts level to level number.
	 * @param mixed $level level name or number
	 */
	public function normalizeLevel($level) {
		//[FIXME: this may break utf-8 if fancy first leter is used]
		$level = trim((string)$level);
		if(!ctype_digit($level)) { //level name
			$lv = ModelSchema::normalize($level);
			if(!isset($this->level_name[$lv])) throw new InvalidArgumentException("Level name not recognized: {$level}. Try number [1..5].");
			$level = $this->level_name[$lv];
		}
		if(!isset($this->level_id[$level])) throw new InvalidArgumentException("Unrecognized level number {$level}. Try number [1..5].");
		return intval($level);
	}
}
?>