<?php

/**
 * ilSelfEvaluationUserdata
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version
 */
class ilSelfEvaluationIdentity
{

    const TABLE_NAME = 'rep_robj_xsev_uid';
    const LENGTH = 6;
    const TYPE_LOGIN = 1;
    const TYPE_EXTERNAL = 2;
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
     * @var int
     */
    protected $type = self::TYPE_LOGIN;

    /**
     * @param $id
     */
    function __construct($id = 0)
    {
        global $DIC;

        $this->id = $id;
        $this->db = $DIC->database();
        //		$this->updateDB();
        if ($id != 0) {
            $this->read();
        }
    }

    public function read()
    {
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
    public function getArrayForDb()
    {
        $e = array();
        foreach (get_object_vars($this) as $k => $v) {
            if (!in_array($k, array('db'))) {
                $e[$k] = array(self::_getType($v), $this->$k);
            }
        }

        return $e;
    }

    final function initDB()
    {
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
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $this->db->createTable(self::TABLE_NAME, $fields);
            $this->db->addPrimaryKey(self::TABLE_NAME, array('id'));
            $this->db->createSequence(self::TABLE_NAME);
        }
    }

    final function updateDB()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $this->initDB();

            return;
        }
        foreach ($this->getArrayForDb() as $k => $v) {
            if (!$this->db->tableColumnExists(self::TABLE_NAME, $k)) {
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

    final private function resetDB()
    {
        $this->db->dropTable(self::TABLE_NAME);
        $this->initDB();
    }

    public function create()
    {
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
    public function delete()
    {
        return $this->db->manipulate('DELETE FROM ' . self::TABLE_NAME . ' WHERE id = '
            . $this->db->quote($this->getId(), 'integer'));
    }

    public function update()
    {
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
     * @return ilSelfEvaluationIdentity[]
     */
    public static function _getAllInstancesByObjId($obj_id, $identifier = null)
    {
        global $DIC;
        $return = array();
        if ($identifier) {
            $set = $DIC->database()->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE obj_id = '
                . $DIC->database()->quote($obj_id,
                    'integer') . ' AND identifier = ' . $DIC->database()->quote($identifier, 'text'));
        } else {
            $set = $DIC->database()->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE obj_id = '
                . $DIC->database()->quote($obj_id, 'integer'));
        }

        while ($rec = $DIC->database()->fetchObject($set)) {
            $return[] = new self($rec->id);
        }

        return $return;
    }

    /**
     * @param $obj_id
     * @param $identifier
     * @return ilSelfEvaluationIdentity
     */
    public static function _getInstanceForObjIdAndIdentifier($obj_id, $identifier)
    {
        global $DIC;

        $set = $DIC->database()->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE obj_id = '
            . $DIC->database()->quote($obj_id, 'integer') . ' AND identifier = ' . $DIC->database()->quote($identifier,
                'text'));

        while ($rec = $DIC->database()->fetchObject($set)) {
            return new self($rec->id);
        }
    }

    /**
     * @param $obj_id
     * @param $identifier
     * @return ilSelfEvaluationIdentity
     */
    public static function _getAllInstancesForObjIdAndIdentifier($obj_id, $identifier)
    {
        return self::_getAllInstancesByObjId($obj_id, $identifier);
    }

    public static function _getNewHashInstanceForObjId($obj_id)
    {
        do {
            $identifier = strtoupper(substr(md5(rand(1, 99999)), 0, self::LENGTH));
        } while (self::_identityExists($obj_id, $identifier));

        $obj = new self();
        $obj->setObjId($obj_id);
        $obj->setIdentifier($identifier);
        $obj->setType(self::TYPE_EXTERNAL);
        $obj->create();

        return $obj;
    }

    public static function _getNewInstanceForObjIdAndUserId($obj_id, $user_id)
    {
        $obj = new self();
        $obj->setObjId($obj_id);
        $obj->setIdentifier($user_id);
        $obj->create();

        return $obj;
    }

    /**
     * @param $obj_id
     * @return ilSelfEvaluationIdentity
     */
    public static function _getNewInstanceForObjId($obj_id)
    {
        $obj = new self();
        $obj->setObjId($obj_id);

        return $obj;
    }

    /**
     * @param $obj_id
     * @param $identifier
     * @return bool
     */
    public static function _identityExists($obj_id, $identifier)
    {
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
     * @return bool
     */
    public static function _getObjIdForIdentityId($identity_id)
    {
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
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $obj_id
     */
    public function setObjId($obj_id)
    {
        $this->obj_id = $obj_id;
    }

    /**
     * @return int
     */
    public function getObjId()
    {
        return $this->obj_id;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }


    //
    // Helper
    //
    /**
     * @param $var
     * @return string
     */
    public static function _getType($var)
    {
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
