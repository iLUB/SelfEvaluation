<?php

/**
 * ilSelfEvaluationData
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version
 */
class ilSelfEvaluationData {

	const TABLE_NAME = 'rep_robj_xsev_d';
	/**
	 * @var int
	 */
	protected $id = 0;
	/**
	 * @var int
	 */
	protected $dataset_id = 0;
	/**
	 * @var int
	 */
	protected $question_id = 0;
	/**
	 * @var int
	 */
	protected $value = NULL;


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
	 * @param int $ref_id
	 *
	 * @return ilSelfEvaluationData
	 */
	public static function _getInstanceByRefId($ref_id) {
		global $ilDB;
		// Existing Object
		$set = $ilDB->query("SELECT * FROM " . self::TABLE_NAME . " " . " WHERE ref_id = "
		. $ilDB->quote($ref_id, "integer"));
		while ($rec = $ilDB->fetchObject($set)) {
			return new self($rec->id);
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
	 * @param int $dataset_id
	 */
	public function setDatasetId($dataset_id) {
		$this->dataset_id = $dataset_id;
	}


	/**
	 * @return int
	 */
	public function getDatasetId() {
		return $this->dataset_id;
	}


	/**
	 * @param int $question_id
	 */
	public function setQuestionId($question_id) {
		$this->question_id = $question_id;
	}


	/**
	 * @return int
	 */
	public function getQuestionId() {
		return $this->question_id;
	}


	/**
	 * @param int $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}


	/**
	 * @return int
	 */
	public function getValue() {
		return $this->value;
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
