<?php

use ilub\plugin\SelfEvaluation\DatabaseHelper\ArrayForDB;
use ilub\plugin\SelfEvaluation\Question\QuestionGUI;
use ilub\plugin\SelfEvaluation\Question\MetaQuestionGUI;
use ilub\plugin\SelfEvaluation\Question\Question;
use ilub\plugin\SelfEvaluation\Question\MetaQuestion;
use ilub\plugin\SelfEvaluation\Identity\Identity;

require_once('class.ilSelfEvaluationData.php');

require_once(dirname(__FILE__) . '/../Feedback/class.ilSelfEvaluationFeedback.php');
require_once(dirname(__FILE__) . '/../Scale/class.ilSelfEvaluationScale.php');

/**
 * ilSelfEvaluationDataset
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version
 */
class ilSelfEvaluationDataset
{
    use ArrayForDB;

    const TABLE_NAME = 'rep_robj_xsev_ds';
    /**
     * @var int
     */
    public $id = 0;
    /**
     * @var int
     */
    protected $identifier_id = 0;
    /**
     * @var int
     */
    protected $creation_date = 0;

    /**
     * @var int
     */
    static protected $highest_scale = 0;

    /**
     * @var bool
     */
    protected $percentage_per_block = false;

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
        if ($id != 0) {
            $this->read();
        }
    }

    public function read()
    {
        $set = $this->db->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE id = '
            . $this->db->quote($this->getId(), 'integer'));
        while ($rec = $this->db->fetchObject($set)) {
            $this->setObjectValuesFromRecord($this, $rec);
        }
    }

    /**
     * @param string $postvar_key
     * @return string|false
     */
    protected function determineQuestionType($postvar_key)
    {
        $type = false;

        if (strpos($postvar_key, QuestionGUI::POSTVAR_PREFIX) === 0) {
            $type = ilSelfEvaluationData::QUESTION_TYPE;
        } else {
            if (strpos($postvar_key, QuestionGUI::POSTVAR_PREFIX) === 0) {
                $type = ilSelfEvaluationData::META_QUESTION_TYPE;
            }
        }

        return $type;
    }

    /**
     * @param string $question_type
     * @param string $postvar_key
     * @return int|false
     */
    protected function getQuestionId($question_type, $postvar_key)
    {
        $qid = false;

        if ($question_type == ilSelfEvaluationData::QUESTION_TYPE) {
            $qid = (int) str_replace(QuestionGUI::POSTVAR_PREFIX, '', $postvar_key);
        } else {
            if ($question_type == ilSelfEvaluationData::META_QUESTION_TYPE) {
                $qid = (int) str_replace(MetaQuestionGUI::POSTVAR_PREFIX, '', $postvar_key);
            }
        }

        return $qid;
    }

    /**
     * @param int    $qid
     * @param string $question_type
     * @return bool
     */
    protected function questionExists($qid, $question_type)
    {
        if ($question_type == ilSelfEvaluationData::QUESTION_TYPE) {
            return Question::_isObject($qid);
        } else {
            if ($question_type == ilSelfEvaluationData::META_QUESTION_TYPE) {
                return MetaQuestion::isObject($qid);
            }
        }

        return false;
    }

    /**
     * @param $post
     * @return array
     */
    protected function getDataFromPost($post)
    {
        $data = [];
        foreach ($post as $k => $v) {
            $type = $this->determineQuestionType($k);
            if ($type === false) {
                continue;
            }
            $qid = $this->getQuestionId($type, $k);
            if ($qid === false) {
                continue;
            }

            if ($this->questionExists($qid, $type)) {
                $data[] = array('qid' => $qid, 'value' => $v, 'type' => $type);
            }
        }

        return $data;
    }

    final function initDB()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $this->db->createTable(self::TABLE_NAME, $this->getArrayForDbWithAttributes());
            $this->db->addPrimaryKey(self::TABLE_NAME, array('id'));
            $this->db->createSequence(self::TABLE_NAME);
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
        $this->db->manipulate('DELETE FROM ' . ilSelfEvaluationData::TABLE_NAME . ' WHERE dataset_id = '
            . $this->db->quote($this->getId(), 'integer'));

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
     * @param $array array (qid => int, value => string, type => string)
     */
    public function saveValuesByArray($array)
    {
        if ($this->getId() == 0) {
            $this->create();
        }

        $qids = [];

        foreach ($array as $item) {
            if (!array_key_exists($item['type'] . $item['qid'], $qids)) {
                $da = new ilSelfEvaluationData();
                $da->setDatasetId($this->getId());
                $da->setQuestionId($item['qid']);
                $da->setValue($item['value']);
                $da->setQuestionType($item['type']);
                $da->setCreationDate(time());
                $da->create();
                $qids[$item['type'] . $item['qid']] = true;
            }
        }
    }

    /**
     * @param $post
     */
    public function saveValuesByPost($post)
    {
        $this->saveValuesByArray($this->getDataFromPost($post));
    }

    /**
     * @param $array array (qid => int, value => string, type => string)
     */
    public function updateValuesByArray($array)
    {
        foreach ($array as $item) {
            $da = ilSelfEvaluationData::_getInstanceForQuestionId($this->getId(), $item['qid'], $item['type']);
            $da->setValue($item['value']);
            $da->update();
        }
    }

    /**
     * @param $post
     */
    public function updateValuesByPost($post)
    {
        $this->updateValuesByArray($this->getDataFromPost($post));
    }

    /**
     * @param $block_id
     * @return mixed
     */
    public function getDataPerBlock($block_id)
    {
        $sum = array();
        foreach (Question::_getAllInstancesForParentId($block_id) as $qst) {
            $da = ilSelfEvaluationData::_getInstanceForQuestionId($this->getId(), $qst->getId());
            $sum[$qst->getId()] = (int) $da->getValue();
        }

        return $sum;
    }

    /**
     * @return array
     */
    public function getMinPercentageBlock()
    {
        $min = 100;
        $min_block_id = null;

        $blocks_percentage = $this->getPercentagePerBlock();

        foreach ($blocks_percentage as $block_id => $percentage) {
            if ($percentage <= $min) {
                $min = round($percentage, 2);
                $min_block_id = $block_id;
            }
        }
        return ['block' => $this->getBlockById($min_block_id), 'percentage' => $min];
    }

    /**
     * @return ilSelfEvaluationBlock[]
     */
    public function getMaxPercentageBlock()
    {
        $max = 0;
        $max_block_id = null;

        $blocks_percentage = $this->getPercentagePerBlock();

        foreach ($blocks_percentage as $block_id => $percentage) {
            if ($percentage >= $max) {
                $max = round($percentage, 2);
                $max_block_id = $block_id;
            }
        }

        return ['block' => $this->getBlockById($max_block_id), 'percentage' => $max];
    }

    /**
     * @return array
     * @description return array(block_id => percentage)
     */
    public function getPercentagePerBlock()
    {
        if (!$this->percentage_per_block) {
            $obj_id = ilSelfEvaluationIdentity::_getObjIdForIdentityId($this->getIdentifierId());

            $this->percentage_per_block = array();
            $highest = $this->getHighestValueFromScale();
            foreach (ilSelfEvaluationQuestionBlock::getAllInstancesByParentId($obj_id) as $block) {
                $answer_data = $this->getDataPerBlock($block->getId());
                if (count($answer_data) == 0) {
                    continue;
                }
                $answer_total = array_sum($answer_data);
                $anzahl_fragen = count($answer_data);
                $possible_per_block = $anzahl_fragen * $highest;
                if ($possible_per_block != 0) {
                    $percentage = $answer_total / $possible_per_block * 100;
                } else {
                    $percentage = 0;
                }

                $this->percentage_per_block[$block->getId()] = $percentage;
            }
        }

        return $this->percentage_per_block;
    }

    /**
     * @return mixed
     */
    public function getHighestValueFromScale()
    {
        if (!self::$highest_scale) {
            $obj_id = ilSelfEvaluationIdentity::_getObjIdForIdentityId($this->getIdentifierId());
            $scale = ilSelfEvaluationScale::_getInstanceByObjId($obj_id)->getUnitsAsArray();
            $sorted_scale = array_keys($scale);
            sort($sorted_scale);
            self::$highest_scale = $sorted_scale[count($sorted_scale) - 1];
        }
        return self::$highest_scale;

    }

    /**
     * @param $block_id
     * @return ilSelfEvaluationBlock
     */
    public function getBlockById($block_id)
    {
        $obj_id = ilSelfEvaluationIdentity::_getObjIdForIdentityId($this->getIdentifierId());

        foreach (ilSelfEvaluationQuestionBlock::getAllInstancesByParentId($obj_id) as $block) {
            if ($block->getId() == $block_id) {
                return $block;
            }
        }
    }

    /**
     * @return float
     */
    public function getOverallPercentage()
    {
        $sum = 0;
        $x = 0;
        foreach ($this->getPercentagePerBlock() as $percentage) {
            $sum += $percentage;
            $x++;
        }

        if ($x == 0) {
            return 100;
        }
        return round($sum / $x, 2);
    }

    /**
     * @return float
     */
    public function getOverallVarianz()
    {
        return round(sqrt($this->getVarianz($this->getPercentagePerBlock(), $this->getOverallPercentage())), 2);
    }

    /**
     * @return float
     */
    public function getOverallStandardabweichung()
    {
        return round(sqrt($this->getOverallVarianz()), 2);
    }

    /**
     * @param $data
     * @return float|int
     */
    protected function getVarianz($data, $average)
    {
        $squared_sum = 0;
        $x = 0;
        foreach ($data as $percentage) {
            $squared_sum += pow($average - $percentage, 2);
            $x++;
        }

        if ($x == 0) {
            return 0;
        }
        return $squared_sum / $x;
    }

    /**
     * @param $data
     * @param $average
     * @return float
     */
    protected function getStandardabweichung($data, $average)
    {
        return sqrt($this->getVarianz($data, $average));
    }

    /**
     * @return array
     * @description return array(block_id => percentage)
     */
    public function getStandardabweichungPerBlock()
    {
        $return = array();
        $obj_id = ilSelfEvaluationIdentity::_getObjIdForIdentityId($this->getIdentifierId());
        $highest = $this->getHighestValueFromScale();

        foreach (ilSelfEvaluationQuestionBlock::getAllInstancesByParentId($obj_id) as $block) {
            $answer_data_mean_percentage = $this->getPercentagePerBlock()[$block->getId()];
            $data_as_percentage = [];

            $answer_data = $this->getDataPerBlock($block->getId());
            if (count($answer_data) == 0) {
                continue;
            }

            foreach ($answer_data as $data) {
                $data_as_percentage[] = $data / $highest * 100;
            }

            $return[$block->getId()] = round($this->getStandardabweichung($data_as_percentage,
                $answer_data_mean_percentage), 2);
        }

        return $return;
    }

    /**
     * @param null $a_block_id
     * @return ilSelfEvaluationFeedback[]
     */
    public function getFeedbacksPerBlock($a_block_id = null)
    {
        $return = array();
        foreach ($this->getPercentagePerBlock() as $block_id => $percentage) {
            $return[$block_id] = ilSelfEvaluationFeedback::_getFeedbackForPercentage($block_id, $percentage);;
        }
        if ($a_block_id) {
            return $return[$a_block_id];
        } else {
            return $return;
        }
    }


    //
    // Static
    //
    /**
     * @param $identifier_id
     * @return ilSelfEvaluationDataset[]
     */
    public static function _getAllInstancesByIdentifierId($identifier_id)
    {
        global $ilDB;
        /**
         * @var $ilDB ilDB
         */
        $return = array();
        $set = $ilDB->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE identifier_id = '
            . $ilDB->quote($identifier_id, 'integer') . ' ORDER BY creation_date ASC');
        while ($rec = $ilDB->fetchObject($set)) {
            $data_set = new ilSelfEvaluationDataset();
            $data_set->setObjectValuesFromRecord($data_set, $rec);
            $return[] = $data_set;
        }

        return $return;
    }

    /**
     * @param      $obj_id
     * @param bool $as_array
     * @return ilSelfEvaluationDataset[]
     */
    public static function _getAllInstancesByObjectId($obj_id, $as_array = false, $identifier = "")
    {
        global $DIC;

        $return = array();
        if ($identifier == "") {
            $identities = ilSelfEvaluationIdentity::_getAllInstancesByObjId($obj_id);
        } else {
            $identities = ilSelfEvaluationIdentity::_getAllInstancesForObjIdAndIdentifier($obj_id, $identifier);
        }

        foreach ($identities as $identity) {
            $set = $DIC->database()->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE identifier_id = '
                . $DIC->database()->quote($identity->getId(), 'integer') . ' ORDER BY creation_date ASC');
            while ($rec = $DIC->database()->fetchObject($set)) {
                if ($as_array) {
                    $return[] = (array) $rec;
                } else {
                    $data_set = new ilSelfEvaluationDataset();
                    $data_set->setObjectValuesFromRecord($data_set, $rec);
                    $return[] = $data_set;
                }
            }
        }

        return $return;
    }

    /**
     * @param      $obj_id
     * @param bool $as_array
     * @return ilSelfEvaluationDataset[]
     */
    public static function _getAllInstancesByObjectIdOfCurrentUser($obj_id)
    {
        global $DIC;

        $return = array();
        foreach (Identity::_getAllInstancesByObjId($obj_id) as $identity) {
            $set = $DIC->database()->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE identifier_id = '
                . $DIC->database()->quote($identity->getId(), 'integer') . ' ORDER BY creation_date ASC');
            while ($rec = $DIC->database()->fetchObject($set)) {

                $data_set = new ilSelfEvaluationDataset();
                $data_set->setObjectValuesFromRecord($data_set, $rec);
                $return[] = $data_set;
            }
        }

        return $return;
    }

    /**
     * @param $obj_id
     * @return bool
     */
    public static function _deleteAllInstancesByObjectId($obj_id)
    {
        foreach (self::_getAllInstancesByObjectId($obj_id) as $obj) {
            $obj->delete();
        }

        return true;
    }

    /**
     * @param $identifier_id
     * @return bool|ilSelfEvaluationDataset
     */
    public static function _getInstanceByIdentifierId($identifier_id)
    {
        global $DIC;

        $set = $DIC->database()->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE identifier_id = '
            . $DIC->database()->quote($identifier_id, 'integer'));
        while ($rec = $DIC->database()->fetchObject($set)) {
            $data_set = new ilSelfEvaluationDataset();
            $data_set->setObjectValuesFromRecord($data_set, $rec);
            return $data_set;
        }

        return false;
    }

    /**
     * @param $identifier_id
     * @return ilSelfEvaluationDataset
     */
    public static function _getNewInstanceForIdentifierId($identifier_id)
    {
        $obj = new self();
        $obj->setIdentifierId($identifier_id);

        return $obj;
    }

    public static function _datasetExists(int $identifier_id) : bool
    {
        global $DIC;

        $set = $DIC->database()->query('SELECT id FROM ' . self::TABLE_NAME . ' ' . ' WHERE identifier_id = '
            . $DIC->database()->quote($identifier_id, 'integer'));
        while ($rec = $DIC->database()->fetchObject($set)) {
            return true;
        }

        return false;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setIdentifierId(int $identifier_id)
    {
        $this->identifier_id = $identifier_id;
    }

    public function getIdentifierId() : int
    {
        return $this->identifier_id;
    }

    public function setCreationDate(int $creation_date)
    {
        $this->creation_date = $creation_date;
    }

    public function getCreationDate() : int
    {
        return $this->creation_date;
    }

    public function getSubmitDate() : int
    {
        $latest_entry = ilSelfEvaluationData::_getLatestInstanceByDatasetId($this->getId());
        if ($latest_entry) {
            return $latest_entry->getCreationDate();
        } else {
            throw new Exception("Invalid Entry");
        }
    }


    public function getDuration() : int
    {
        $latest_entry = ilSelfEvaluationData::_getLatestInstanceByDatasetId($this->getId());
        if ($latest_entry) {
            return $latest_entry->getCreationDate() - $this->getCreationDate();
        } else {
            throw new Exception("Invalid Entry");
        }
    }
}