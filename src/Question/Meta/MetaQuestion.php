<?php
namespace ilub\plugin\SelfEvaluation\Question\Meta;

use ilub\plugin\SelfEvaluation\DatabaseHelper\hasDBFields;
use SimpleXMLElement;
use ilub\plugin\SelfEvaluation\DatabaseHelper\ArrayForDB;
use ilDBInterface;

class MetaQuestion implements hasDBFields
{
    use ArrayForDB;

    const TABLE_NAME = 'rep_robj_xsev_mqst';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $container_id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $short_title;

    /**
     * @var int
     */
    protected $type_id;

    /**
     * @var array
     */
    protected $values;

    /**
     * @var bool
     */
    protected $required;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var \ilDBInterface
     */
    protected $db;


    public function __construct(ilDBInterface $db, int $container_id, int $id = 0)
    {

        $this->db = $db;

        $this->setId($id);
        $this->setContainerId($container_id);

        if ($this->getId() > 0) {
            $this->read();
        }
    }

    public function cloneTo(ilDBInterface $db, int $parent_id): MetaQuestion
    {
        $clone = new self($db, $parent_id);
        $clone->setContainerId($this->getContainerId());
        $clone->setName($this->getName());
        $clone->setShortTitle($this->getShortTitle());
        $clone->setTypeId($this->getTypeId());
        $clone->setValues($this->getValues());
        $clone->enableRequired($this->isRequired());
        $clone->setPosition($this->getPosition());
        $clone->setContainerId($parent_id);
        $clone->update();
        return $clone;
    }

    public function toXml(SimpleXMLElement $xml): SimpleXMLElement
    {
        $child_xml = $xml->addChild("metaQuestion");
        $child_xml->addAttribute("containerId", $this->getContainerId());
        $child_xml->addAttribute("name", $this->getName());
        $child_xml->addAttribute("shortTitle", $this->getShortTitle());
        $child_xml->addAttribute("typeId", $this->getTypeId());
        $child_xml->addAttribute("values", serialize($this->getValues()));
        $child_xml->addAttribute("enableRequired", $this->isRequired());
        $child_xml->addAttribute("position", $this->getPosition());
        return $xml;
    }

    public static function fromXml(ilDBInterface $db, int $parent_id, SimpleXMLElement $xml): SimpleXMLElement
    {
        $attributes = $xml->attributes();
        $question = new self($db, $parent_id);
        $question->setName($attributes["name"]);
        $question->setShortTitle($attributes["shortTitle"]);
        $question->setTypeId((int) $attributes["typeId"]);
        $question->setValues(unserialize($attributes["values"]));
        $question->enableRequired($attributes["enableRequired"] == "1" ? true : false);
        $question->setPosition((int) $attributes["position"]);
        $question->update();
        return $xml;
    }

    public function initDB()
    {
        if (self::TABLE_NAME != '' AND !$this->db->tableExists(self::TABLE_NAME)) {
            $this->db->createTable(self::TABLE_NAME, $this->getArrayForDbWithAttributes());
            $this->db->createSequence(self::TABLE_NAME);
            $this->db->addPrimaryKey(self::TABLE_NAME, ['field_id']);
        }
    }


    protected function getNextPosition(): int
    {
        $stmt = $this->db->query('SELECT MAX(position) next_pos FROM ' .self::TABLE_NAME.
            ' WHERE container_id = '.$this->getContainerId().';');
        while ($rec = $this->db->fetchObject($stmt)) {
            return $rec->next_pos + 1;
        }

        return 1;
    }

    public function create()
    {
        if ($this->getId() != 0) {
            $this->update();
            return;
        }
        $this->setId($this->db->nextID(self::TABLE_NAME));
        $this->setPosition($this->getNextPosition());
        $this->db->insert(self::TABLE_NAME, $this->getArrayForDb());
    }

    public function update()
    {
        if ($this->getId() == 0) {
            $this->create();
            return;
        }
        $this->db->update(self::TABLE_NAME, $this->getArrayForDb(), $this->getIdForDb());
    }

    public function delete()
    {
        $stmt = $this->db->prepare('DELETE FROM ' . self::TABLE_NAME . ' WHERE field_id = ?;',
            ['integer']);
        $this->db->execute($stmt, [$this->getId()]);
    }

    protected function read()
    {
        $stmt = $this->db->prepare('SELECT * FROM ' . self::TABLE_NAME . ' WHERE field_id = ? ORDER BY position ASC;',
            ['integer']);
        $this->db->execute($stmt, [$this->getId()]);
        $row = $stmt->fetchObject();

        $this->setObjectValuesFromRecord($this,$row);
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setContainerId(int $container_id)
    {
        $this->container_id = $container_id;
    }

    public function getContainerId(): int
    {
        return $this->container_id;
    }

    public function getTypeId(): int
    {
        return $this->type_id;
    }

    public function setTypeId(int $type)
    {
        $this->type_id = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getShortTitle(): string
    {
        return $this->short_title;
    }

    public function setShortTitle(string $short_title)
    {
        $this->short_title = $short_title;
    }

    public function getValues(): array
    {
        return $this->values ? $this->values : [];
    }

    public function setValues(array $values)
    {
        $this->values = $values;
    }

    public function isRequired():bool
    {
        return $this->required;
    }

    public function enableRequired(bool $status)
    {
        $this->required = $status;
    }

    public function setPosition(int $position)
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getPosition():int
    {
        return $this->position;
    }

    public static function _isObject(ilDBInterface $db, int $field_id)
    {
        $set = $db->query('SELECT field_id FROM '.self::TABLE_NAME.' WHERE field_id = '.$field_id);

        while ($rec = $db->fetchObject($set)) {
            return true;
        }

        return false;
    }

    /**
     * @param ilDBInterface $db
     * @param int           $parent_id
     * @param bool          $as_array
     * @return self[]
     */
    public static function _getAllInstancesForParentId(ilDBInterface $db, int $parent_id, bool $as_array = false) : array
    {
        $query = 'SELECT * FROM '.self::TABLE_NAME.' WHERE container_id = '.$parent_id.' ORDER BY position ASC';

        $set = $db->query($query);

        $return = [];

        while ($rec = $db->fetchObject($set)) {
            if ($as_array) {
                $return[$rec->field_id] = (array) new self($db, $parent_id, $rec->field_id);
            } else {
                $return[$rec->field_id] = new self($db, $parent_id, $rec->field_id);
            }
        }

        return $return;
    }
}