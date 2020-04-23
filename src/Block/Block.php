<?php
namespace ilub\plugin\SelfEvaluation\Block;

use ilub\plugin\SelfEvaluation\DatabaseHelper\ArrayForDB;
use ilDBInterface;
use ilub\plugin\SelfEvaluation\DatabaseHelper\hasDBFields;
use SimpleXMLElement;
use ilCtrl;
use ilSelfEvaluationPlugin;

abstract class Block implements hasDBFields
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
     * @var ilDBInterface
     */
    protected $db;

    function __construct(ilDBInterface $db, $id = 0)
    {
        $this->db = $db;

        $this->id = $id;
        if ($id != 0) {
            $this->read();
        }
    }

    abstract public function cloneTo(int $parent_id);

    abstract function toXml(SimpleXMLElement $xml) : SimpleXMLElement;

    static abstract function fromXml(ilDBInterface $db,int $parent_id, SimpleXMLElement $xml) : SimpleXMLElement;

    public function read()
    {
        $set = $this->db->query('SELECT * FROM ' . self::getTableName() . ' ' . ' WHERE id = '
            . $this->db->quote($this->getId(), 'integer'));
        $this->setObjectValuesFromRecord($this,$this->db->fetchObject($set));
    }

    abstract static public function getTableName() : string;

    public function initDB()
    {
        if (!$this->db->tableExists(self::getTableName())) {
            $this->db->createTable(self::getTableName(), $this->getArrayForDbWithAttributes());
            $this->db->addPrimaryKey(self::getTableName(), ['id']);
            $this->db->createSequence(self::getTableName());
        }
    }

    final function updateDB()
    {
        if (!$this->db->tableExists(self::getTableName())) {
            $this->initDB();

            return;
        }
        foreach ($this->getArrayForDbWithAttributes() as $property => $attributes) {
            if (!$this->db->tableColumnExists(self::getTableName(), $property)) {
                $this->db->addTableColumn(self::getTableName(), $property, $attributes);
            }
        }
    }

    public function create()
    {
        $this->setId($this->db->nextID(self::getTableName()));
        $this->setPosition(BlockFactory::_getNextPositionAcrossBlocks($this->db, $this->getParentId()));
        $this->db->insert(self::getTableName(), $this->getArrayForDb());
    }

    /**
     * @return int
     */
    public function delete()
    {

        return $this->db->manipulate('DELETE FROM ' . self::getTableName() . ' WHERE id = '
            . $this->db->quote($this->getId(), 'integer'));
    }

    public function update()
    {
        if ($this->getId() == 0) {
            $this->create();

            return;
        }
        $this->db->update(self::getTableName(), $this->getArrayForDb(), $this->getIdForDb());
    }

    /**
     * @param ilDBInterface $db
     * @param int           $parent_id
     * @return self[]
     */
    public static function _getAllInstancesByParentId(ilDBInterface $db, int $parent_id)
    {
        $return = [];
        $set = $db->query('SELECT * FROM ' . static::getTableName() . ' ' . ' WHERE parent_id = '.$parent_id. ' ORDER BY position ASC');
        while ($rec = $db->fetchObject($set)) {
            $block = new static($db);
            $block->setObjectValuesFromRecord( $block, $rec);
            $return[] = $block;
        }

        return $return;
    }

    public function getNextPosition(int $parent_id)
    {
        $set = $this->db->query('SELECT MAX(position) next_pos FROM ' . self::getTableName() . ' ' . ' WHERE parent_id = '
            . $this->db->quote($parent_id, 'integer'));
        while ($rec = $this->db->fetchObject($set)) {
            return $rec->next_pos + 1;
        }

        return 1;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function setParentId(int $parent_id)
    {
        $this->parent_id = $parent_id;
    }

    public function getParentId() : int
    {
        return $this->parent_id;
    }

    public function setPosition(int $position)
    {
        $this->position = $position;
    }

    public function getPosition() : int
    {
        return $this->position;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getPositionId() : string
    {
        return get_class($this) . '_' . $this->getId();
    }

    abstract function getBlockTableRow(ilDBInterface $db, ilCtrl $ilCtrl, ilSelfEvaluationPlugin $plugin) : BlockTableRow;
}