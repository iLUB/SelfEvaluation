<?php

use ilub\plugin\SelfEvaluation\DatabaseHelper\ArrayForDB;
use ilDBInterface;

/**
 * Class ilSelfEvaluationBlock
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
abstract class ilSelfEvaluationBlock
{
    use ArrayForDB;

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
     * @var bool
     */
    protected $sortable;

    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @param $id
     */
    function __construct($id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();

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
    static abstract function fromXml(ilDBInterface $db, $parent_id, SimpleXMLElement $xml);

    public function read()
    {
        $set = $this->db->query('SELECT * FROM ' . $this->getTableName() . ' ' . ' WHERE id = '
            . $this->db->quote($this->getId(), 'integer'));
        while ($rec = $this->db->fetchObject($set)) {
            static::setObjectValuesFromRecord($this, $rec);
        }
    }

    /**
     * @return string
     */
    static public function getTableName()
    {
    }

    public function initDB()
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
        if (!$this->db->tableExists($this->getTableName())) {
            $this->db->createTable($this->getTableName(), $fields);
            $this->db->addPrimaryKey($this->getTableName(), array('id'));
            $this->db->createSequence($this->getTableName());
        }
    }

    final function updateDB()
    {
        if (!$this->db->tableExists($this->getTableName())) {
            $this->initDB();

            return;
        }
        foreach ($this->getArrayForDbWithAttributes() as $property => $attributes) {
            if (!$this->db->tableColumnExists(self::TABLE_NAME, $property)) {
                $this->db->addTableColumn(self::TABLE_NAME, $property, $attributes);
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

    public function create()
    {
        if ($this->getId() != 0) {
            $this->update();

            return;
        }
        $this->setId($this->db->nextID($this->getTableName()));
        require_once(dirname(__FILE__) . '/class.ilSelfEvaluationBlockFactory.php');
        $this->setPosition(ilSelfEvaluationBlockFactory::getNextPositionAcrossBlocks($this->getParentId()));
        $this->db->insert($this->getTableName(), $this->getArrayForDb());
    }

    /**
     * @return int
     */
    public function delete()
    {

        return $this->db->manipulate('DELETE FROM ' . $this->getTableName() . ' WHERE id = '
            . $this->db->quote($this->getId(), 'integer'));
    }

    public function update()
    {
        if ($this->getId() == 0) {
            $this->create();

            return;
        }
        $this->db->update($this->getTableName(), $this->getArrayForDb(), $this->getIdForDb());
    }

    public function isBlockSortable(): bool
    {
        if($this->sortable === null){
            $this->sortable = $this->initSortable();
        }
        return $this->sortable;
    }

    protected function initSortable()
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
        global $DIC;
        /**
         * @var $DIC ILIAS\DI\Container
         */

        $return = array();
        $set = $DIC->database()->query('SELECT * FROM ' . static::getTableName() . ' ' . ' WHERE parent_id = '
            . $DIC->database()->quote($parent_id, 'integer') . ' ORDER BY position ASC');
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
        $set = $this->db->database()->query('SELECT MAX(position) next_pos FROM ' . $this->getTableName() . ' ' . ' WHERE parent_id = '
            . $this->db->database()->quote($parent_id, 'integer'));
        while ($rec = $this->db->database()->fetchObject($set)) {
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
}