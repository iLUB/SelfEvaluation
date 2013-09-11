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
	const LENGTH = 10;
	/**
	 * @var int
	 */
	protected $id = 0;
	/**
	 * @var string
	 */
	protected $identifier = '';
	/**
	 * @var int
	 */
	protected $obj_id = 0;


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
	 *
	 * @return ilSelfEvaluationIdentity[]
	 */
	public static function _getAllInstancesByForForObjId($obj_id) {
		global $ilDB;
		$return = array();
		$set = $ilDB->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE obj_id = '
		. $ilDB->quote($obj_id, 'integer'));
		while ($rec = $ilDB->fetchObject($set)) {
			$return[] = new self($rec->id);
		}

		return $return;
	}


	/**
	 * @param $obj_id
	 * @param $identifier
	 *
	 * @return ilSelfEvaluationIdentity
	 */
	public static function _getInstanceForObjId($obj_id, $identifier) {
		global $ilDB;
		if ($identifier === NULL) {
			$identifier = substr(md5(rand(1, 99999)), 0, self::LENGTH);
			$set = $ilDB->query('SELECT identifier FROM ' . self::TABLE_NAME . ' ' . ' WHERE identifier = '
			. $ilDB->quote($identifier, 'text'));
			while (! $rec = $ilDB->fetchObject($set)) {
				break;
			}
		}
		$set = $ilDB->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE obj_id = '
		. $ilDB->quote($obj_id, 'integer') . ' AND identifier = ' . $ilDB->quote($identifier, 'text'));
		while ($rec = $ilDB->fetchObject($set)) {
			return new self($rec->id);
		}
		$obj = new self();
		$obj->setObjId($obj_id);
		$obj->setIdentifier($identifier);
		$obj->create();

		return $obj;
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
		. $ilDB->quote($obj_id, 'integer') . ' AND identifier = ' . $ilDB->quote($identifier, 'text'));
		while ($rec = $ilDB->fetchObject($set)) {
			return true;
		}

		return false;
	}


	/**
	 * @param $identity_id
	 *
	 * @return bool
	 */
	public static function _getObjIdForIdentityId($identity_id) {
		global $ilDB;
		$set = $ilDB->query('SELECT obj_id FROM ' . self::TABLE_NAME . ' ' . ' WHERE id = '
		. $ilDB->quote($identity_id, 'integer'));
		while ($rec = $ilDB->fetchObject($set)) {
			return $rec->obj_id;
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
	 * @param string $identifier
	 */
	public function setIdentifier($identifier) {
		$this->identifier = $identifier;
	}


	/**
	 * @return string
	 */
	public function getIdentifier() {
		return $this->identifier;
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
