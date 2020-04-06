<?php

/**
 * Class ilSelfEvaluationBlock
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
abstract class ilSelfEvaluationBlock
{

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
    function __construct($id = 0)
    {
        $this->id = $id;

        //		 $this->updateDB();
        if ($id != 0) {
            $this->read();
        }
    }

    /**
     * @param $parent_id
     * @return ilSelfEvaluationBlock
     */
    abstract public function cloneTo($parent_id);

    /**
     * @param SimpleXMLElement $xml
     * @return SimpleXMLElement
     */
    abstract function toXml(SimpleXMLElement $xml);

    /**
     * @param                  $parent_id
     * @param SimpleXMLElement $xml
     * @return mixed
     */
    static abstract function fromXml($parent_id, SimpleXMLElement $xml);

    public function read()
    {
        global $DIC;
        /**
         * @var $DIC ILIAS\DI\Container
         */

        $set = $DIC->database()->query('SELECT * FROM ' . $this->getTableName() . ' ' . ' WHERE id = '
            . $DIC->database()->quote($this->getId(), 'integer'));
        while ($rec = $DIC->database()->fetchObject($set)) {
            static::setObjectValuesFromRecord($this, $rec);
        }
    }

    /**
     * @return array
     */
    public function getArrayForDb()
    {
        $e = array();
        foreach (get_object_vars($this) as $k => $v) {
            if (!in_array($k, $this->getNonDbFields())) {
                $e[$k] = array(self::_getType($v), $this->$k);
            }
        }

        return $e;
    }

    /**
     * @return array
     */
    protected function getNonDbFields()
    {
        return array('db');
    }

    /**
     * @return string
     */
    static public function getTableName()
    {
    }

    public function initDB()
    {
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
        if (!$DIC->database()->tableExists($this->getTableName())) {
            $DIC->database()->createTable($this->getTableName(), $fields);
            $DIC->database()->addPrimaryKey($this->getTableName(), array('id'));
            $DIC->database()->createSequence($this->getTableName());
        }
    }

    final function updateDB()
    {
        global $DIC;
        /**
         * @var $DIC ILIAS\DI\Container
         */

        if (!$DIC->database()->tableExists($this->getTableName())) {
            $this->initDB();

            return;
        }
        foreach ($this->getArrayForDb() as $k => $v) {
            if (!$DIC->database()->tableColumnExists($this->getTableName(), $k)) {
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
                $DIC->database()->addTableColumn($this->getTableName(), $k, $field);
            }
        }
    }

    /**
     * @param ilSelfEvaluationBlock $block
     * @param stdClass              $rec
     */
    protected static function setObjectValuesFromRecord(
        ilSelfEvaluationBlock &$block = null,
        $rec = null
    ) {
        foreach ($block->getArrayForDb() as $k => $v) {
            $block->{$k} = $rec->{$k};
        }
    }

    final private function resetDB()
    {
        global $DIC;
        /**
         * @var $DIC ILIAS\DI\Container
         */

        $DIC->database()->dropTable($this->getTableName());
        $this->initDB();
    }

    public function create()
    {
        global $DIC;
        /**
         * @var $DIC ILIAS\DI\Container
         */

        if ($this->getId() != 0) {
            $this->update();

            return;
        }
        $this->setId($DIC->database()->nextID($this->getTableName()));
        require_once(dirname(__FILE__) . '/class.ilSelfEvaluationBlockFactory.php');
        $this->setPosition(ilSelfEvaluationBlockFactory::getNextPositionAcrossBlocks($this->getParentId()));
        $DIC->database()->insert($this->getTableName(), $this->getArrayForDb());
    }

    /**
     * @return int
     */
    public function delete()
    {
        global $DIC;
        /**
         * @var $DIC ILIAS\DI\Container
         */

        return $DIC->database()->manipulate('DELETE FROM ' . $this->getTableName() . ' WHERE id = '
            . $DIC->database()->quote($this->getId(), 'integer'));
    }

    public function update()
    {
        global $DIC;
        /**
         * @var $DIC ILIAS\DI\Container
         */

        if ($this->getId() == 0) {
            $this->create();

            return;
        }
        $DIC->database()->update($this->getTableName(), $this->getArrayForDb(), array(
            'id' => array(
                'integer',
                $this->getId()
            ),
        ));
    }

    /**
     * @return bool
     */
    public function isBlockSortable()
    {
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
     * @param int $parent_id
     * @return ilSelfEvaluationBlock[]
     */
    public static function getAllInstancesByParentId($parent_id)
    {
        global $ilDB;
        $return = array();
        $set = $ilDB->query('SELECT * FROM ' . static::getTableName() . ' ' . ' WHERE parent_id = '
            . $ilDB->quote($parent_id, 'integer') . ' ORDER BY position ASC');
        while ($rec = $ilDB->fetchObject($set)) {
            /** @var ilSelfEvaluationBlock $block */
            $block = new static();
            static::setObjectValuesFromRecord($block, $rec);
            $return[] = $block;
        }

        return $return;
    }

    /**
     * @param $parent_id
     * @return int
     */
    public function getNextPosition($parent_id)
    {
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
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
     * @return ilSelfEvaluationBlockTableRow
     */
    abstract public function getBlockTableRow();

    public function getPositionId()
    {
        return get_class($this) . '_' . $this->getId();
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