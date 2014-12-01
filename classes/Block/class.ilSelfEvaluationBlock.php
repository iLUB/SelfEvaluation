<?php
require_once(dirname(__FILE__) . '/../Scale/class.ilSelfEvaluationScale.php');
/**
 * ilSelfEvaluationBlock
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version
 */
class ilSelfEvaluationBlock {

	const TABLE_NAME = 'rep_robj_xsev_block';
	/**
	 * @var int
	 */
	public $id = 0;
	/**
	 * @var string
	 */
	protected $title = '';
	/**
	 * @var string
	 */
	protected $description = '';
	/**
	 * @var string
	 */
	protected $abbreviation = '';
	/**
	 * @var int
	 */
	protected $position = 99;
	/**
	 * @var int
	 */
	protected $parent_id = 0;
	/**
	 * @var ilSelfEvaluationScale
	 */
	protected $scale;


	/**
	 * @param $id
	 */
	function __construct($id = 0) {
		global $ilDB;
		/**
		 * @var $ilDB ilDB
		 */
		$this->id = $id;
		$this->db = $ilDB;
		//		 $this->updateDB();
		if ($id != 0) {
			$this->read();
		}
	}


	public function read() {
		$set = $this->db->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE id = '
			. $this->db->quote($this->getId(), 'integer'));
		while ($rec = $this->db->fetchObject($set)) {
			foreach ($this->getArrayForDb() as $k => $v) {
				$this->{$k} = $rec->{$k};
			}
		}
		$this->setScale(ilSelfEvaluationScale::_getInstanceByRefId($this->getParentId()));
	}


	/**
	 * @return array
	 */
	public function getArrayForDb() {
		$e = array();
		foreach (get_object_vars($this) as $k => $v) {
			$non_db_fields = array( 'db', 'scale' );
			if (! in_array($k, $non_db_fields)) {
				$e[$k] = array( self::_getType($v), $this->$k );
			}
		}

		return $e;
	}


	final function initDB() {
		$fields = array();
		foreach ($this->getArrayForDb() as $k => $v) {
			$fields[$k] = array(
				'type' => $v[0],
			);
			switch ($v[0]) {
				case 'integer':
					$fields[$k]['length'] = 4;
					break;
				case 'text':
					$fields[$k]['length'] = 1024;
					break;
			}
			if ($k == 'id') {
				$fields[$k]['notnull'] = true;
			}
		}
		if (! $this->db->tableExists(self::TABLE_NAME)) {
			$this->db->createTable(self::TABLE_NAME, $fields);
			$this->db->addPrimaryKey(self::TABLE_NAME, array( 'id' ));
			$this->db->createSequence(self::TABLE_NAME);
		}
	}


	final function updateDB() {
		if (! $this->db->tableExists(self::TABLE_NAME)) {
			$this->initDB();

			return;
		}
		foreach ($this->getArrayForDb() as $k => $v) {
			if (! $this->db->tableColumnExists(self::TABLE_NAME, $k)) {
				$field = array(
					'type' => $v[0],
				);
				switch ($v[0]) {
					case 'integer':
						$field['length'] = 4;
						break;
					case 'text':
						$field['length'] = 1024;
						break;
				}
				if ($k == 'id') {
					$field['notnull'] = true;
				}
				$this->db->addTableColumn(self::TABLE_NAME, $k, $field);
			}
		}
	}


	final private function resetDB() {
		$this->db->dropTable(self::TABLE_NAME);
		$this->initDB();
	}


	public function create() {
		if ($this->getId() != 0) {
			$this->update();

			return;
		}
		$this->setId($this->db->nextID(self::TABLE_NAME));
		$this->setPosition(self::_getNextPosition($this->getParentId()));
		$this->db->insert(self::TABLE_NAME, $this->getArrayForDb());
	}


	/**
	 * @return int
	 */
	public function delete() {
		return $this->db->manipulate('DELETE FROM ' . self::TABLE_NAME . ' WHERE id = '
			. $this->db->quote($this->getId(), 'integer'));
	}


	public function update() {
		if ($this->getId() == 0) {
			$this->create();

			return;
		}
		$this->db->update(self::TABLE_NAME, $this->getArrayForDb(), array(
			'id' => array(
				'integer',
				$this->getId()
			),
		));
	}


	/**
	 * @return bool
	 */
	public function isBlockSortable() {
		/**
		 * @var $parentObject ilObjSelfEvaluation
		 */
		$object_factory = new ilObjectFactory();
		$parentObject = $object_factory->getInstanceByObjId($this->getParentId());
		switch ($parentObject->getSortType()) {
			case ilObjSelfEvaluation::SHUFFLE_OFF:
				return true;
			case ilObjSelfEvaluation::SHUFFLE_IN_BLOCKS:
				return false;
			default:
				return false;
		}
	}


	//
	// Static
	//
	/**
	 * @param int  $parent_id ilObjSelfEvaluation obj id
	 * @param bool $as_array
	 *
	 * @return ilSelfEvaluationBlock[]
	 */
	public static function _getAllInstancesByParentId($parent_id, $as_array = false) {
		global $ilDB;
		$return = array();
		$set = $ilDB->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE parent_id = '
			. $ilDB->quote($parent_id, 'integer') . ' ORDER BY position ASC');
		while ($rec = $ilDB->fetchObject($set)) {
			if ($as_array) {
				$return[] = (array)new self($rec->id);
			} else {
				$return[] = new self($rec->id);
			}
		}

		return $return;
	}


	/**
	 * @param $parent_id
	 *
	 * @return int
	 */
	public static function _getNextPosition($parent_id) {
		global $ilDB;
		$set = $ilDB->query('SELECT MAX(position) next_pos FROM ' . self::TABLE_NAME . ' ' . ' WHERE parent_id = '
			. $ilDB->quote($parent_id, 'integer'));
		while ($rec = $ilDB->fetchObject($set)) {
			return $rec->next_pos + 1;
		}

		return 1;
	}


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}


	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}


	/**
	 * @param string $abbreviation
	 */
	public function setAbbreviation($abbreviation) {
		$this->abbreviation = $abbreviation;
	}


	/**
	 * @return string
	 */
	public function getAbbreviation() {
		return $this->abbreviation;
	}


	/**
	 * @param int $parent_id
	 */
	public function setParentId($parent_id) {
		$this->parent_id = $parent_id;
	}


	/**
	 * @return int
	 */
	public function getParentId() {
		return $this->parent_id;
	}


	/**
	 * @param int $position
	 */
	public function setPosition($position) {
		$this->position = $position;
	}


	/**
	 * @return int
	 */
	public function getPosition() {
		return $this->position;
	}


	/**
	 * @param \ilSelfEvaluationScale $scale
	 */
	public function setScale($scale) {
		$this->scale = $scale;
	}


	/**
	 * @return \ilSelfEvaluationScale
	 */
	public function getScale() {
		return $this->scale;
	}


	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}


	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}


	//
	// Helper
	//
	/**
	 * @param $var
	 *
	 * @return string
	 */
	public static function _getType($var) {
		switch (gettype($var)) {
			case 'string':
			case 'array':
			case 'object':
				return 'text';
			case 'NULL':
			case 'boolean':
				return 'integer';
			default:
				return gettype($var);
		}
	}
}

?>