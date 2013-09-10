<?php

/**
 * ilSelfEvaluationQuestion
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version
 */
class ilSelfEvaluationQuestion {

	const TABLE_NAME = 'rep_robj_xsev_qst';
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
	protected $question_body = '';
	/**
	 * @var int
	 */
	protected $position = 0;
	/**
	 * @var bool
	 */
	protected $is_inverse = false;
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
	}


	/**
	 * @return array
	 */
	public function getArrayForDb() {
		$e = array();
		foreach (get_object_vars($this) as $k => $v) {
			if (! in_array($k, array( 'db' ))) {
				$e[$k] = array( self::_getType($v), $this->$k );
			}
		}

		return $e;
	}


	final function initDB() {
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


	final private function resetDB() {
		$this->db->dropTable(self::TABLE_NAME);
		$this->initDB();
	}


	public function create() {
		if ($this->getId() != 0) {
			$this->update();

			return true;
		}
		$this->setId($this->db->nextID(self::TABLE_NAME));
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
		$this->db->update(self::TABLE_NAME, $this->getArrayForDb(), array(
			'id' => array(
				'integer',
				$this->getId()
			),
		));
	}


	//
	// Static
	//
	/**
	 * @param      $parent_id
	 * @param bool $as_array
	 *
	 * @return ilSelfEvaluationQuestion[]
	 */
	public static function _getAllInstancesForParentId($parent_id, $as_array = false) {
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
	 * @param      $parent_id
	 * @param bool $as_array
	 *
	 * @return ilSelfEvaluationQuestion[]
	 */
	public static function _getInstancesForQuestionId($parent_id, $as_array = false) {
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
	 * @param $id
	 *
	 * @return bool
	 */
	public static function _isObject($id) {
		global $ilDB;
		$set = $ilDB->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE id = ' . $ilDB->quote($id, 'integer'));
		while ($rec = $ilDB->fetchObject($set)) {
			return true;
		}

		return false;
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
	 * @param string $question_body
	 */
	public function setQuestionBody($question_body) {
		$this->question_body = $question_body;
	}


	/**
	 * @return string
	 */
	public function getQuestionBody() {
		return $this->question_body;
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


	/**
	 * @param boolean $is_inverse
	 */
	public function setIsInverse($is_inverse) {
		$this->is_inverse = $is_inverse;
	}


	/**
	 * @return boolean
	 */
	public function getIsInverse() {
		return $this->is_inverse;
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
