<?php
namespace ilub\plugin\SelfEvaluation\Question;

use ilub\plugin\SelfEvaluation\DatabaseHelper\ArrayForDB;
use ilub\plugin\SelfEvaluation\DatabaseHelper\hasDBFields;
use SimpleXMLElement;
use ilDBInterface;

class Question implements hasDBFields
{
    use ArrayForDB;

    const TABLE_NAME = 'rep_robj_xsev_qst';
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
    protected $question_body = '';
    /**
     * @var int
     */
    protected $position = 99;
    /**
     * @var bool
     */
    protected $is_inverse = false;
    /**
     * @var int
     */
    protected $parent_id = 0;

    /**
     * @var array
     */
    static protected $instances_for_parent_id_array = [];

    /**
     * @var array
     */
    static protected $instances_for_parent_id = [];

    /**
     * @var \ilDBInterface
     */
    protected $db;


    function __construct(ilDBInterface $db, $id = 0)
    {
        $this->db = $db;

        if ($id != 0) {
            $this->read();
        }
    }

    public function cloneTo(int $parent_id): Question
    {
        $clone = new self($this->db);
        $clone->setParentId($parent_id);
        $clone->setTitle($this->getTitle());
        $clone->setQuestionBody($this->getQuestionBody());
        $clone->setPosition($this->getPosition());
        $clone->setIsInverse($this->getIsInverse());
        $clone->update();
        return $clone;
    }

    public function toXml(SimpleXMLElement $xml): SimpleXMLElement
    {
        $child_xml = $xml->addChild("question");
        $child_xml->addAttribute("parentId", $this->getParentId());
        $child_xml->addAttribute("title", $this->getTitle());
        $child_xml->addAttribute("questionBody", $this->getQuestionBody());
        $child_xml->addAttribute("position", $this->getPosition());
        $child_xml->addAttribute("inverse", $this->getIsInverse());
        return $xml;
    }

    public static function fromXml(ilDBInterface $db, int $parent_id, SimpleXMLElement $xml): SimpleXMLElement
    {
        $attributes = $xml->attributes();
        $question = new self($db);
        $question->setParentId($parent_id);
        $question->setTitle($attributes["title"]);
        $question->setQuestionBody($attributes["questionBody"]);
        $question->setIsInverse((int)$attributes["inverse"]);
        $question->create();
        $question->setPosition((int)$attributes["position"]);
        $question->update();
        return $xml;
    }

    public function read()
    {
        $set = $this->db->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE id = '.$this->getId());

        $this->setObjectValuesFromRecord($this,$this->db->fetchObject($set));
    }


    final function initDB()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $this->db->createTable(self::TABLE_NAME, $this->getArrayForDbWithAttributes());
            $this->db->addPrimaryKey(self::TABLE_NAME, ['id']);
            $this->db->createSequence(self::TABLE_NAME);
        }
    }

    final function updateDB()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $this->initDB();
            return;
        }
        foreach ($this->getArrayForDbWithAttributes() as $property => $attributes) {
            if (!$this->db->tableColumnExists(self::TABLE_NAME, $property)) {
                $this->db->addTableColumn(self::TABLE_NAME, $property, $attributes);
            }
        }
    }

    public function create()
    {
        if ($this->getId() != 0) {
            $this->update();

            return;
        }
        $this->setId($this->db->nextID(self::TABLE_NAME));
        $this->setPosition(self::_getNextPosition($this->db,$this->getParentId()));
        $this->db->insert(self::TABLE_NAME, $this->getArrayForDb());
    }

    public function delete(): int
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
        $this->db->update(self::TABLE_NAME, $this->getArrayForDb(), $this->getIdForDb());
    }

    /**
     * @param ilDBInterface $db
     * @param int           $parent_id
     * @param bool          $as_array
     * @return Question[]
     */
    public static function _getAllInstancesForParentId(ilDBInterface $db, int $parent_id, bool $as_array = false)
    {
        $set = $db->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE parent_id = '
            . $db->quote($parent_id, 'integer') . ' ORDER BY position ASC');

        if ($as_array) {
            if (!self::$instances_for_parent_id_array[$parent_id]) {
                self::$instances_for_parent_id_array[$parent_id] = [];
                while ($rec = $db->fetchObject($set)) {
                    self::$instances_for_parent_id_array[$parent_id][$rec->id] = (array) new self($db,$rec->id);
                }
            }
            return self::$instances_for_parent_id_array[$parent_id];
        } else {
            if (!self::$instances_for_parent_id[$parent_id]) {
                self::$instances_for_parent_id[$parent_id] = [];

                while ($rec = $db->fetchObject($set)) {
                    self::$instances_for_parent_id[$parent_id][$rec->id] = new self($db,$rec->id);
                }
            }
            return self::$instances_for_parent_id[$parent_id];
        }

    }

    public static function _isObject(ilDBInterface $db, int $id): bool
    {
        $set = $db->query('SELECT id FROM ' . self::TABLE_NAME . ' WHERE id = ' .$id);
        while ($rec = $db->fetchObject($set)) {
            return true;
        }

        return false;
    }

    public static function _getNextPosition(ilDBInterface $db, int $parent_id): int
    {
        $set = $db->query('SELECT MAX(position) next_pos FROM ' . self::TABLE_NAME . ' ' . ' WHERE parent_id = '.$parent_id);
        while ($rec = $db->fetchObject($set)) {
            return $rec->next_pos + 1;
        }

        return 1;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setPosition(int $position)
    {
        $this->position = $position;
    }

    public function getPosition(): int
    {
        return $this->position;
    }


    public function setIsInverse(bool $is_inverse)
    {
        $this->is_inverse = $is_inverse;
    }

    public function getIsInverse(): int
    {
        return $this->is_inverse;
    }

    public function setParentId(int $parent_id)
    {
        $this->parent_id = $parent_id;
    }

    public function getParentId(): int
    {
        return $this->parent_id;
    }

    public function setQuestionBody(string $question_body)
    {
        $this->question_body = $question_body;
    }

    public function getQuestionBody(): string
    {
        return $this->question_body;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
