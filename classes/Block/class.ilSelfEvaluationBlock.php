<?php
/**
 * Class ilSelfEvaluationBlock
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
abstract class ilSelfEvaluationBlock {

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
	 * @var int
	 */
	protected $position = 99;
	/**
	 * @var int
	 */
	protected $parent_id = 0;


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
		$set = $this->db->query('SELECT * FROM ' . $this->getTableName() . ' ' . ' WHERE id = '
			. $this->db->quote($this->getId(), 'integer'));
		while ($rec = $this->db->fetchObject($set)) {
			foreach ($this->getArrayForDb() as $k => $v) {
				$this->{$k} = $rec->{$k};
			}
		}
	}


	/**
	 * @return array
	 */
	public function getArrayForDb() {
		$e = array();
		foreach (get_object_vars($this) as $k => $v) {
			if (! in_array($k, $this->getNonDbFields())) {
				$e[$k] = array( self::_getType($v), $this->$k );
			}
		}

		return $e;
	}


	/**
	 * @return array
	 */
	protected function getNonDbFields() {
		return array( 'db' );
	}


	/**
	 * @return string
	 */
	abstract public function getTableName();


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
		if (! $this->db->tableExists($this->getTableName())) {
			$this->db->createTable($this->getTableName(), $fields);
			$this->db->addPrimaryKey($this->getTableName(), array( 'id' ));
			$this->db->createSequence($this->getTableName());
		}
	}


	final function updateDB() {
		if (! $this->db->tableExists($this->getTableName())) {
			$this->initDB();

			return;
		}
		foreach ($this->getArrayForDb() as $k => $v) {
			if (! $this->db->tableColumnExists($this->getTableName(), $k)) {
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
				$this->db->addTableColumn($this->getTableName(), $k, $field);
			}
		}
	}


	final private function resetDB() {
		$this->db->dropTable($this->getTableName());
		$this->initDB();
	}


	public function create() {
		if ($this->getId() != 0) {
			$this->update();

			return;
		}
		$this->setId($this->db->nextID($this->getTableName()));
		$this->setPosition($this->getNextPosition($this->getParentId()));
		$this->db->insert($this->getTableName(), $this->getArrayForDb());
	}


	/**
	 * @return int
	 */
	public function delete() {
		return $this->db->manipulate('DELETE FROM ' . $this->getTableName() . ' WHERE id = '
			. $this->db->quote($this->getId(), 'integer'));
	}


	public function update() {
		if ($this->getId() == 0) {
			$this->create();

			return;
		}
		$this->db->update($this->getTableName(), $this->getArrayForDb(), array(
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
	protected function getAllInstancesByParentId($parent_id, $as_array = false) {
		global $ilDB;
		$return = array();
		$set = $ilDB->query('SELECT * FROM ' . static::getTableName() . ' ' . ' WHERE parent_id = '
			. $ilDB->quote($parent_id, 'integer') . ' ORDER BY position ASC');
		while ($rec = $ilDB->fetchObject($set)) {
			if ($as_array) {
				$return[] = (array)new static($rec->id);
			} else {
				$return[] = new static($rec->id);
			}
		}

		return $return;
	}


	/**
	 * @param $parent_id
	 *
	 * @return int
	 */
	public function getNextPosition($parent_id) {
		global $ilDB;
		$set = $ilDB->query('SELECT MAX(position) next_pos FROM ' . $this->getTableName() . ' ' . ' WHERE parent_id = '
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