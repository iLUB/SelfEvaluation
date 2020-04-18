<?php

/**
 * ilSelfEvaluationFeedback
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version
 */
class ilSelfEvaluationFeedback
{

    const TABLE_NAME = 'rep_robj_xsev_fb';
    /**
     * @var int
     */
    public $id = 0;
    /**
     * @var int
     */
    protected $parent_id = 0;
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
    protected $start_value = 0;
    /**
     * @var int
     */
    protected $end_value = 100;
    /**
     * @var string
     */
    protected $feedback_text = '';

    /**
     * @var bool
     */
    protected $parent_type_overall = false;

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
     * @return ilSelfEvaluationFeedback
     */
    public function cloneTo($parent_id)
    {
        $clone = new self();
        $clone->setParentId($parent_id);
        $clone->setTitle($this->getTitle());
        $clone->setDescription($this->getDescription());
        $clone->setStartValue($this->getStartValue());
        $clone->setEndValue($this->getEndValue());
        $clone->setFeedbackText($this->getFeedbackText());
        $clone->setParentTypeOverall($this->isParentTypeOverall());
        $clone->update();
        return $clone;
    }

    /**
     * @param SimpleXMLElement $xml
     * @return SimpleXMLElement
     */
    public function toXml(SimpleXMLElement $xml)
    {
        $child_xml = $xml->addChild("feedback");
        $child_xml->addAttribute("parentId", $this->getParentId());
        $child_xml->addAttribute("title", $this->getTitle());
        $child_xml->addAttribute("description", $this->getDescription());
        $child_xml->addAttribute("startValue", $this->getStartValue());
        $child_xml->addAttribute("endValue", $this->getEndValue());
        $child_xml->addAttribute("feedbackText", $this->getFeedbackText());
        $child_xml->addAttribute("parentTypeOverall", $this->isParentTypeOverall());

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
        $question = new self();
        $question->setParentId($parent_id);
        $question->setTitle($attributes["title"]);
        $question->setDescription($attributes["description"]);
        $question->setStartValue($attributes["startValue"]);
        $question->setEndValue($attributes["endValue"]);
        $question->setFeedbackText($attributes["feedbackText"]);
        $question->setParentTypeOverall($attributes["parentTypeOverall"]);
        $question->create();
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
     * @param      $parent_id
     * @param bool $as_array
     * @return ilSelfEvaluationFeedback[]
     */
    public static function _getAllInstancesForParentId($parent_id, $as_array = false, $is_overall = false)
    {
        global $ilDB;
        $return = array();
        $q = 'SELECT * FROM ' . self::TABLE_NAME . ' ' .
            ' WHERE parent_id = ' . $ilDB->quote($parent_id, 'integer');

        if ($is_overall) {
            $q .= ' AND parent_type_overall = ' . $ilDB->quote($is_overall, 'integer');
        }
        $q .= ' ORDER BY start_value ASC';

        $set = $ilDB->query($q);

        while ($rec = $ilDB->fetchObject($set)) {
            if ($as_array) {
                $return[] = (array) new self($rec->id);
            } else {
                $return[] = new self($rec->id);
            }
        }

        return $return;
    }

    /**
     * @param      $parent_id
     * @param bool $as_array
     * @return ilSelfEvaluationFeedback[]
     */
    public static function _getAllInstances($is_overall = false)
    {
        global $ilDB;
        $return = [];
        $q = 'SELECT * FROM ' . self::TABLE_NAME . ' ';

        if ($is_overall) {
            $q .= ' WHERE parent_type_overall = ' . $ilDB->quote($is_overall, 'integer');
        }
        $set = $ilDB->query($q);

        while ($rec = $ilDB->fetchObject($set)) {
            $return[] = new self($rec->id);

        }

        return $return;
    }

    /**
     * @param      $parent_id
     * @param      $percentage
     * @param bool $is_overall
     * @return bool|ilSelfEvaluationFeedback
     */
    public static function _getFeedbackForPercentage($parent_id, $percentage, $is_overall = false)
    {
        global $ilDB;
        $q = 'SELECT id FROM ' . self::TABLE_NAME . ' ' . ' WHERE parent_id = ' . $ilDB->quote($parent_id, 'integer')
            . ' AND start_value <= ' . $ilDB->quote($percentage, 'float')
            . ' AND end_value >= ' . $ilDB->quote($percentage, 'float');
        if ($is_overall) {
            $q .= ' AND parent_type_overall = ' . $ilDB->quote($is_overall, 'integer');
        }
        $set = $ilDB->query($q);

        while ($res = $ilDB->fetchObject($set)) {
            return new self($res->id);
        }

        return false;
    }

    /**
     * @param      $parent_id
     * @param int  $value
     * @param int  $ignore
     * @param bool $is_overall
     * @return int
     */
    public static function _getNextMinValueForParentId($parent_id, $value = 0, $ignore = 0, $is_overall = false)
    {
        global $ilDB;
        for ($return = $value; $return < 100; $return++) {
            $q =
                'SELECT id FROM ' . self::TABLE_NAME . ' ' . ' WHERE parent_id = ' . $ilDB->quote($parent_id, 'integer')
                . ' AND start_value <= ' . $ilDB->quote($return, 'integer')
                . ' AND end_value > ' . $ilDB->quote($return, 'integer');
            if ($ignore) {
                $q .= ' AND id != ' . $ilDB->quote($ignore, 'integer');
            }
            if ($is_overall) {
                $q .= ' AND parent_type_overall = ' . $ilDB->quote($is_overall, 'integer');
            }
            $set = $ilDB->query($q);
            $res = $ilDB->fetchObject($set);
            if (!$res->id) {
                return $return;
            }
        }

        return 100;
    }

    /**
     * @param      $parent_id
     * @param int  $value
     * @param int  $ignore
     * @param bool $is_overall
     * @return int
     */
    public static function _getNextMaxValueForParentId($parent_id, $value = 0, $ignore = 0, $is_overall = false)
    {
        global $ilDB;
        /**
         * @var $ilDB ilDB
         */
        for ($return = $value + 1; $return <= 100; $return++) {
            $q =
                'SELECT id FROM ' . self::TABLE_NAME . ' ' . ' WHERE parent_id = ' . $ilDB->quote($parent_id, 'integer')
                . ' AND start_value <= ' . $ilDB->quote($return, 'integer')
                . ' AND end_value >= ' . $ilDB->quote($return, 'integer');
            if ($ignore) {
                $q .= ' AND id != ' . $ilDB->quote($ignore, 'integer');
            }
            if ($is_overall) {
                $q .= ' AND parent_type_overall = ' . $ilDB->quote($is_overall, 'integer');
            }
            $set = $ilDB->query($q);
            $res = $ilDB->fetchObject($set);
            if ($res->id) {
                return $return;
            }

        }

        return 100;
    }

    /**
     * @param $parent_id
     * @return bool
     */
    public static function _isComplete($parent_id, $is_overall = false)
    {
        $min = self::_getNextMinValueForParentId($parent_id, 0, 0, $is_overall);
        $max = self::_getNextMaxValueForParentId($parent_id, $min, 0, $is_overall);

        return ($min == 100 AND $max == 100) ? true : false;
    }

    /**
     * @param $parent_id
     * @param $start_value
     * @param $end_value
     * @return bool
     */
    public static function _hasOverlap($parent_id, $start_value, $end_value)
    { // TODO delete?
        global $ilDB;

        return true;

        return false;
    }

    /**
     * @param $parent_id
     * @return ilSelfEvaluationFeedback
     */
    public static function _getNewInstanceByParentId($parent_id, $is_overall = false)
    {
        $obj = new self();
        $obj->setParentId($parent_id);
        $obj->setParentTypeOverall($is_overall);

        return $obj;
    }

    /**
     * @param      $parent_id
     * @param bool $is_overall
     * @return int
     */
    public static function _rearangeFeedbackLinear($parent_id, $is_overall = false)
    {
        $obj = new self();
        $obj->setParentId($parent_id);

        $feedbacks = self::_getAllInstancesForParentId($parent_id, $as_array = false, $is_overall);
        $nr_feedbacks = count($feedbacks) + 1;
        $range_per_feedback = (int) floor(100 / $nr_feedbacks);
        $remainder = 100 - $range_per_feedback * $nr_feedbacks;

        $start = 0;
        foreach ($feedbacks as $feedback) {
            $range = $range_per_feedback;
            if ($remainder > 0) {
                $range++;
                $remainder -= 1;
            }
            $feedback->setStartValue($start);
            $end = $start + $range;
            $feedback->setEndValue($end);
            $feedback->update();
            $start = $end;
        }

        return $range_per_feedback;
    }

    //
    // Setter & Getter
    //
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
     * @param int $end_value
     */
    public function setEndValue($end_value)
    {
        $this->end_value = $end_value;
    }

    /**
     * @return int
     */
    public function getEndValue()
    {
        return $this->end_value;
    }

    /**
     * @param string $feedback_text
     */
    public function setFeedbackText($feedback_text)
    {
        $this->feedback_text = $feedback_text;
    }

    /**
     * @return string
     */
    public function getFeedbackText()
    {
        return $this->feedback_text;
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
     * @param int $start_value
     */
    public function setStartValue($start_value)
    {
        $this->start_value = $start_value;
    }

    /**
     * @return int
     */
    public function getStartValue()
    {
        return $this->start_value;
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

    /**
     * @return bool
     */
    public function isParentTypeOverall()
    {
        return $this->parent_type_overall;
    }

    /**
     * @param bool $parent_type_overall
     */
    public function setParentTypeOverall($parent_type_overall)
    {
        $this->parent_type_overall = $parent_type_overall;
    }
}

?>
