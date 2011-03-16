<?php

require_once('JLogger.class.php');


/**
* Handles categories.
* 
* The level registration is used by politicians. When assigning new function (appointment), the category
* must be chosen from the same region.level.
* 
* The raadsstukken may use categories freely.
*
* @author Sardar Yumatov (ja.doma@gmail.com)
*/
class CategoryModel {

	/** DB table name containing all categories. */
	const TABLE_NAME = 'sys_categories';
	/** used by lastInertId() to retrieve the last inserted ID. null for MySQL */
	const ID_SEQUENCE = 'sys_categories_id_seq';

	/** subdescriptions. */
	const LEVEL_CAT_TABLE = 'sys_category_regions';


	protected $id;
	protected $name;
	protected $key;
	protected $description;

	/** @array (level.id => description) */
	protected $levels;

	protected $schema;
	protected $db;


	/**
	 * Construct new unresolved category.
	 *
	 * @param string $name category name
	 * @param string $description category description
	 */
	public function __construct($name, $description = null) {
		$this->id = null;
		$this->name = $name;
		$this->key = self::stem($name);
		$this->description = $description;

		$this->schema = null;
		$this->db = null;

		$this->levels = array();
	}


	/**
	 * Resolve this object.
	 * This method is for internal use only!
	 *
	 * @access package
	 * @param CategoryModelSchema $schema the parent schema
	 * @param PDO $db database link
	 * @param integer $id row id
	 * @param array $levels level description
	 */
	public function resolve(CategoryModelSchema $schema, $db, $id, $levels) {
		$log = JLogger::getLogger('utils.import.schema.category');

		$this->schema = $schema;
		$this->db = $db;
		$this->levels = $levels;

		if($id === null) {
			if(defined('DRY_RUN')) throw new RuntimeException("Before inserting new category: ".$this->name);

			if(defined('FULL_ACCESS') && !FULL_ACCESS)
				throw new RuntimeException('Not allowed to insert new category: '.$this->name);

			$log->preUpdate("Inserting new category: {$this->name}");

			$ins = $this->db->prepare('INSERT INTO '.self::TABLE_NAME.' (name, description) VALUES(:name, :description);');
			$ins->execute(array(
				':name' => $this->name,
				':description' => $this->description,
			));

			if($ins->rowCount() != 1) throw new RuntimeException("Can't insert new category: {$this->name}");

			if(self::ID_SEQUENCE) $this->id = $this->db->lastInsertId(self::ID_SEQUENCE);
			else $this->id = $this->db->lastInsertId();

			$log->postUpdate("Category '{$this->name}' is successfully inserted with id: {$this->id}");

			unset($ins);
		} else  $this->id = $id;
	}


	/**
	 * Returns record ID of this category.
	 * @return integer
	 */
	public function getId() {
		if($this->id === null) throw new RuntimeException("Category '{$this->name}' is not yet resolved!");
		return $this->id;
	}

	/**
	 * Returns <tt>CategoryModelSchema</tt>.
	 * @return CategoryModelSchema
	 */
	public function getSchema() {
		if($this->schema == null) throw new RuntimeException("Category '{$this->name}' is not yet resolved");
		return $this->schema;
	}

	/**
	 * Returns category name.
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Returns stemmed category name.
	 * @return string
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * Returns category description.
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Returns per-level category descriptions.
	 * @return array (level => description)
	 */
	public function getLevelDescriptions() {
		return $this->levels;
	}


	/**
	 * Register this category with specific level.
	 *
	 * @param integer $level level number, starting from 1
	 * @param string $description additional description
	 * @return void
	 */
	public function registerLevelDescription($level, $description = null) {
		$log = JLogger::getLogger('utils.import.schema.category');

		if(defined('FULL_ACCESS') && !FULL_ACCESS)
			throw new RuntimeException('Not allowed to insert/update category description for category: '.$this->name);

		$level = intval($level);
		if(array_key_exists($level, $this->levels)) {
			if(trim($this->levels[$level]) == trim($description)) return;

			$log->preUpdate("Updating existing category level registration for level: {$level} and category '{$this->name}'");
			$stm = $this->db->prepare('UPDATE '.self::LEVEL_CAT_TABLE.' SET description = :description WHERE category = :category AND level = :level;');
		} else {
			$log->preUpdate("Inserting new category level registration for level: {$level} and category '{$this->name}'");
			$stm = $this->db->prepare('INSERT INTO '.self::LEVEL_CAT_TABLE.'(category, level, description) VALUES(:category, :level, :description);');
		}

		if(defined('DRY_RUN')) throw new RuntimeException("Before inserting/updating new level({$level}) for category: ".$this->name);

		$stm->execute(array(
			':level' => $level,
			':category' => $this->id,
			':description' => $description,
		));

		if($stm->rowCount() != 1) throw new RuntimeException("Can't register category '{$this->name}' for level '{$level}'");

		$log->postUpdate("Successfully inserted new (category, level) registration.");

		$this->levels[$level] = $description;
	}

	/**
	 * Create category level registration if is is *NOT* exists.
	 * If category is registered, it will be left untouched.
	 * 
	 * The main difference with <tt>registerLevelDescription()</tt> is that
	 * this method will not update the $description if category is already registered.
	 *
	 * @param integer $level the region level [1..5]
	 * @param string $description optional description for new registration
	 * @return void
	 */
	public function ensureLevelRegistered($level, $description = null) {	
		$level = intval($level);
		if(!array_key_exists($level, $this->levels)) $this->registerLevelDescription($level, $description);
	}
	
	
	/**
	 * Returns true if category is registered at specific level.
	 *
	 * @param integer $level the region level [1..5]
	 * @return boolean true - level registration exists, otherwise false
	 */
	public function isInLevel($level) {
		$level = intval($level);
		return array_key_exists($level, $this->levels);
	}

	/**
	 * Stem the given key.
	 * @param string $key key to stem
	 * @return string stemmed string
	 */
	public static function stem($key) {
		if(defined('DISABLE_STEM_CATEGORY')) return ModelSchema::plainNormalize($key);
		return ModelSchema::normalize($key);
	}


	/**
	 * Serialize this category to the DOM tree.
	 *
	 * @param DOMDocument $dom the owner document, used to create elements
	 * @param DOMElement $root where to 'category' element will be added
	 * @param array $options extra options
	 * @return void
	 */
	public function toXml($dom, $root, $options = null) {
		$el = $dom->createElement('category');
		$el->setAttribute('name', $this->name);
		$el->setAttribute('description', $this->description);
		$root->appendChild($el);

		foreach ($this->levels as $l => $d) {
			$lv = $dom->createElement('inlevel');
			$lv->setAttribute('level', $l);
			$lv->setAttribute('description', $d);
			$el->appendChild($lv);
		}
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
		
		$xw->startElement('category'); // <category>
		$xw->writeAttribute('name', $this->name);
		$xw->writeAttribute('description', $this->description);
		
		foreach ($this->levels as $l => $d) {
			$xw->startElement('inlevel'); // <inlevel>
			$xw->writeAttribute('level', $l);
			$xw->writeAttribute('description', $d);
			$xw->endElement(); // </inlevel>
		}
		
		$xw->endElement(); // </category>
	}
	
	public function touch() {
		$this->schema->trace($this->id);
	}
}


?>