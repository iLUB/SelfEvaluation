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
	protected $position = 99;
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
		$this->id = $id;
		// $this->updateDB();
		if ($id != 0) {
			$this->read();
		}
	}


	public function read() {
		global $DIC;
		/**
		 * @var $DIC ILIAS\DI\Container
		 */
		
		$set = $DIC->database()->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE id = '
		. $DIC->database()->quote($this->getId(), 'integer'));
		while ($rec = $DIC->database()->fetchObject($set)) {
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
		global $DIC;
		/**
		 * @var $DIC ILIAS\DI\Container
		 */
		
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
		if (! $DIC->database()->tableExists(self::TABLE_NAME)) {
			$DIC->database()->createTable(self::TABLE_NAME, $fields);
			$DIC->database()->addPrimaryKey(self::TABLE_NAME, array( 'id' ));
			$DIC->database()->createSequence(self::TABLE_NAME);
		}
	}


	final function updateDB() {
		global $DIC;
		/**
		 * @var $DIC ILIAS\DI\Container
		 */
		
		if (! $DIC->database()->tableExists(self::TABLE_NAME)) {
			$this->initDB();

			return;
		}
		foreach ($this->getArrayForDb() as $k => $v) {
			if (! $DIC->database()->tableColumnExists(self::TABLE_NAME, $k)) {
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
				$DIC->database()->addTableColumn(self::TABLE_NAME, $k, $field);
			}
		}
	}


	final private function resetDB() {
		global $DIC;
		/**
		 * @var $DIC ILIAS\DI\Container
		 */
		
		$DIC->database()->dropTable(self::TABLE_NAME);
		$this->initDB();
	}


	public function create() {
		global $DIC;
		/**
		 * @var $DIC ILIAS\DI\Container
		 */
		
		if ($this->getId() != 0) {
			$this->update();

			return;
		}
		$this->setId($DIC->database()->nextID(self::TABLE_NAME));
		$this->setPosition(self::_getNextPosition($this->getParentId()));
		$DIC->database()->insert(self::TABLE_NAME, $this->getArrayForDb());
	}


	/**
	 * @return int
	 */
	public function delete() {
		global $DIC;
		/**
		 * @var $DIC ILIAS\DI\Container
		 */
		
		return $DIC->database()->manipulate('DELETE FROM ' . self::TABLE_NAME . ' WHERE id = '
		. $DIC->database()->quote($this->getId(), 'integer'));
	}


	public function update() {
		global $DIC;
		/**
		 * @var $DIC ILIAS\DI\Container
		 */
		
		if ($this->getId() == 0) {
			$this->create();

			return;
		}
		$DIC->database()->update(self::TABLE_NAME, $this->getArrayForDb(), array(
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
	 * @param int  $parent_id is a block id
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
				$return[$rec->id] = (array)new self($rec->id);
			} else {
				$return[$rec->id] = new self($rec->id);
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
		$set = $ilDB->query('SELECT id FROM ' . self::TABLE_NAME . ' WHERE id = ' . $ilDB->quote($id, 'integer'));
		while ($rec = $ilDB->fetchObject($set)) {
			return true;
		}

		return false;
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
