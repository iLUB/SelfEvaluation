<?php
require_once('class.ilSelfEvaluationScaleUnit.php');
/**
 * ilSelfEvaluationScale
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version
 */
class ilSelfEvaluationScale {

	const TABLE_NAME = 'rep_robj_xsev_scale';
	/**
	 * @var int
	 */
	protected $id = 0;
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
		//		$this->initDB();
		if ($id != 0) {
			$this->read();
		}
		$this->units = ilSelfEvaluationScaleUnit::_getAllInstancesByParentId($this->getId());
	}

    /**
     * @param $parent_obj_id
     * @return ilSelfEvaluationScale
     */
	public function cloneTo($parent_obj_id){
		$clone = new self();
		$clone->setParentId($parent_obj_id);
		$clone->update();
        $old_units = ilSelfEvaluationScaleUnit::_getAllInstancesByParentId($this->getId());
		$new_units = [];

        foreach ($old_units as $old_unit){
            $new_units[] = $old_unit->cloneTo($clone->getId());
		}
        $clone->units = $new_units;

        return $clone;
	}

	/**
	 * @param SimpleXMLElement $xml
	 * @return SimpleXMLElement
	 */
	public function toXml(SimpleXMLElement $xml){
		$child_xml = $xml->addChild("scale");
		$units = ilSelfEvaluationScaleUnit::_getAllInstancesByParentId($this->getId());

		foreach ($units as $unit){
			$child_xml = $unit->toXml($this->getId(),$child_xml);
		}

		return $xml;
	}

	/**
	 * @param bool $flipped
	 *
	 * @return array (unit value => unit title)
	 */
	public function getUnitsAsArray($flipped = false) {
		$return = array();
		foreach ($this->units as $k => $u) {
			if ($flipped) {
				$return[$this->units[count($this->units) - $k - 1]->getValue()] = $u->getTitle();
			} else {
				$return[$u->getValue()] = $u->getTitle();
			}
		}

		return $return;
	}

	public function hasUnits(){
		return count($this->units)>0;
	}

    /**
     * @return array
     */
    public function getUnitsAsRelativeArray() {
        $return = array();
        $min_max = $this->getMinMaxValue();
        $max = $min_max['max'];

        foreach ($this->units as $k => $u) {
        	$return[$u->getValue()*100/$max] = $u->getTitle(). " (".$u->getValue().")";
        }

        return $return;
    }

    /**
     * @return array
     */
    public function getMinMaxValue() {
        $min = 999999;
        $max = 0;
        foreach ($this->units as $k => $u) {
        	if($u->getValue() > $max){
        		$max = $u->getValue();
			}
            if($u->getValue() < $min){
                $min = $u->getValue();
            }
        }

        return ['min'=>$min,'max'=>$max];
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
			if (! in_array($k, array( 'db', 'units' ))) {
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


	final private function resetDB() {
		$this->db->dropTable(self::TABLE_NAME);
		$this->initDB();
	}


	/**
	 * @return bool
	 */
	public function create() {
		if ($this->getId() != 0) {
			$this->update();

			return;
		}
		$this->setId($this->db->nextID(self::TABLE_NAME));
		$this->db->insert(self::TABLE_NAME, $this->getArrayForDb());
	}


	/**
	 * @return int
	 */
	public function delete() {
		$this->db->manipulate('DELETE FROM ' . self::TABLE_NAME . ' WHERE id = '
		. $this->db->quote($this->getId(), 'integer'));
	}


	public function update() {
		if ($this->getId() == 0) {
			$this->create();
		}
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
	 * @param $parent_id
	 *
	 * @return ilSelfEvaluationScale
	 */
	public static function _getInstanceByObjId($parent_obj_id) {
		global $ilDB;
		// Existing Object
		$set = $ilDB->query("SELECT * FROM " . self::TABLE_NAME . " " . " WHERE parent_id = "
		. $ilDB->quote($parent_obj_id, "integer"));
		while ($rec = $ilDB->fetchObject($set)) {
			return new self($rec->id);
		}
		$obj = new self();
		$obj->setParentId($parent_obj_id);

		return $obj;
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
	 * @return int
	 */
	public function getAmount() {
		return count($this->units);
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
