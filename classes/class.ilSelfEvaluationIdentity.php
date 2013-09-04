<?php

/**
 * ilSelfEvaluationUserdata
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version
 */
class ilSelfEvaluationIdentity {

	const TABLE_NAME = 'rep_robj_xsev_uid';
	const TYPE_USER = 1;
	const TYPE_ANONYMOUS = 2;
	/**
	 * @var int
	 */
	protected $id = 0;
	/**
	 * @var int
	 */
	protected $identifier_type = self::TYPE_USER;
	/**
	 * @var int
	 */
	protected $user_id = 0;
	/**
	 * @var string
	 */
	protected $text_key = '';
	/**
	 * @var int
	 */
	protected $obj_id = 0;
	/**
	 * @var int
	 */
	protected $type = self::TYPE_USER;


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
		//		$this->initDB();
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
	 * @param $obj_id
	 * @param $identifier
	 *
	 * @return bool|ilSelfEvaluationIdentity
	 */
	public static function _getInstanceByForForObjId($obj_id, $identifier) {
		global $ilDB;
		// Existing Object
		$set = $ilDB->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE obj_id = '
		. $ilDB->quote($obj_id, 'integer') . ' AND (user_id = ' . $ilDB->quote($identifier, 'integer')
		. ' OR text_key = ' . $ilDB->quote($identifier, 'text') . ')');
		while ($rec = $ilDB->fetchObject($set)) {
			return new self($rec->id);
		}

		return false;
	}


	/**
	 * @param $obj_id
	 *
	 * @return ilSelfEvaluationIdentity
	 */
	public static function _getNewInstanceForObjId($obj_id) {
		$obj = new self();
		$obj->setObjId($obj_id);

		return $obj;
	}


	/**
	 * @param $obj_id
	 * @param $identifier
	 *
	 * @return bool
	 */
	public static function _identityExists($obj_id, $identifier) {
		global $ilDB;
		$set = $ilDB->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE obj_id = '
		. $ilDB->quote($obj_id, 'integer') . ' AND (user_id = ' . $ilDB->quote($identifier, 'integer')
		. ' OR text_key = ' . $ilDB->quote($identifier, 'text') . ')');
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
	 * @param int $identifier_type
	 */
	public function setIdentifierType($identifier_type) {
		$this->identifier_type = $identifier_type;
	}


	/**
	 * @return int
	 */
	public function getIdentifierType() {
		return $this->identifier_type;
	}


	/**
	 * @param int $obj_id
	 */
	public function setObjId($obj_id) {
		$this->obj_id = $obj_id;
	}


	/**
	 * @return int
	 */
	public function getObjId() {
		return $this->obj_id;
	}


	/**
	 * @param string $text_key
	 */
	public function setTextKey($text_key) {
		$this->text_key = $text_key;
	}


	/**
	 * @return string
	 */
	public function getTextKey() {
		return $this->text_key;
	}


	/**
	 * @param int $user_id
	 */
	public function setUserId($user_id) {
		$this->user_id = $user_id;
	}


	/**
	 * @return int
	 */
	public function getUserId() {
		return $this->user_id;
	}


	/**
	 * @param int $type
	 */
	public function setType($type) {
		$this->type = $type;
	}


	/**
	 * @return int
	 */
	public function getType() {
		return $this->type;
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
