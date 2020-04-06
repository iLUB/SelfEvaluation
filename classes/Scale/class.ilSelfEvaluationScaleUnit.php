<?php

/**
 * ilSelfEvaluationScaleUnit
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version
 */
class ilSelfEvaluationScaleUnit
{

    const TABLE_NAME = 'rep_robj_xsev_scale_u';
    /**
     * @var int
     */
    protected $id = 0;
    /**
     * @var string
     */
    protected $title = 'Standartitle';
    /**
     * @var int
     */
    protected $value = 10;
    /**
     * @var int
     */
    protected $parent_id = 0;
    /**
     * @var int
     */
    protected $position = 99;

    /**
     * @param $id
     */
    function __construct($id = 0)
    {
        global $ilDB;
        /**
         * @var $ilDB ilDB
         */
        $this->id = $id;
        $this->db = $ilDB;
        //		$this->updateDB();
        if ($id != 0) {
            $this->read();
        }
    }

    /**
     * @param $parent_id
     * @return ilSelfEvaluationScaleUnit
     */
    public function cloneTo($parent_id)
    {
        $clone = new self();
        $clone->setParentId($parent_id);
        $clone->setTitle($this->getTitle());
        $clone->setValue($this->getValue());
        $clone->setPosition($this->getPosition());
        $clone->update();
        return $clone;
    }

    /**
     * @param                  $parent_id
     * @param SimpleXMLElement $xml
     * @return SimpleXMLElement
     */
    public function toXml($parent_id, SimpleXMLElement $xml)
    {
        $child_xml = $xml->addChild("scaleUnit");
        $child_xml->addAttribute("parentId", $parent_id);
        $child_xml->addAttribute("title", $this->getTitle());
        $child_xml->addAttribute("value", $this->getValue());
        $child_xml->addAttribute("position", $this->getPosition());
        return $xml;
    }

    /**
     * @param                  $parent_id
     * @param SimpleXMLElement $xml
     * @return SimpleXMLElement
     */
    public static function fromXml($parent_id, SimpleXMLElement $xml)
    {
        $attributes = $xml->attributes();
        $unit = new self();
        $unit->setParentId($parent_id);
        $unit->setTitle($attributes["title"]);
        $unit->setValue($attributes["value"]);
        $unit->setPosition($attributes["position"]);
        $unit->create();
        return $xml;
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


    //
    // Static
    //
    /**
     * @param $parent_id
     * @return ilSelfEvaluationScaleUnit[]
     */
    public static function _getAllInstancesByParentId($parent_id)
    {
        global $ilDB;
        $return = array();
        $set = $ilDB->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE parent_id = '
            . $ilDB->quote($parent_id, 'integer') . ' ORDER BY position ASC');
        while ($rec = $ilDB->fetchObject($set)) {
            $return[] = new self($rec->id);
        }

        return $return;
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
     * @param int $parent_id
     */
    public function setParentId($parent_id)
    {
        $this->parent_id = $parent_id;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param int $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
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