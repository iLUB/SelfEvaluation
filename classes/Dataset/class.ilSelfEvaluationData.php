<?php
use ilub\plugin\SelfEvaluation\DatabaseHelper\ArrayForDB;
/**
 * ilSelfEvaluationData
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version
 */
class ilSelfEvaluationData
{
    use ArrayForDB;

    const TABLE_NAME = 'rep_robj_xsev_d';
    const QUESTION_TYPE = 'qst';
    const META_QUESTION_TYPE = 'mqst';
    /**
     * @var int
     */
    protected $id = 0;
    /**
     * @var int
     */
    protected $dataset_id = 0;
    /**
     * @var int
     */
    protected $question_id = 0;
    /**
     * @var string
     */
    protected $question_type = '';
    /**
     * @var int
     */
    protected $creation_date = 0;
    /**
     * @var string
     */
    protected $value = '';

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

        $this->id = $id;
        $this->db = $DIC->database();
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
     * @param $data
     * @param $rec
     * @return $this
     */


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
        return $this->db->manipulate('DELETE FROM ' . self::TABLE_NAME . ' WHERE id = '
            . $this->db->quote($this->getId(), 'integer'));
    }

    /**
     * @return bool
     */
    public function update()
    {
        if ($this->getId() == 0) {
            $this->create();

            return;
        }
        $this->db->update(self::TABLE_NAME, $this->getArrayForDb(), $this->getIdForDb());
    }


    //
    // Static
    //
    /**
     * @param $dataset_id
     * @return ilSelfEvaluationData[]
     */
    public static function _getAllInstancesByDatasetId($dataset_id)
    {
        global $ilDB;
        $return = array();
        $set = $ilDB->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE dataset_id = '
            . $ilDB->quote($dataset_id, 'integer'));
        while ($rec = $ilDB->fetchObject($set)) {
            $data = new ilSelfEvaluationData();
            $data->setObjectValuesFromRecord($data, $rec);

            $return[] = $data;
        }

        return $return;
    }

    /**
     * @param $dataset_id
     * @return ilSelfEvaluationData
     */
    public static function _getLatestInstanceByDatasetId($dataset_id)
    {
        global $ilDB;

        $set = $ilDB->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE dataset_id = '
            . $ilDB->quote($dataset_id, 'integer') . ' ORDER BY creation_date LIMIT 1');
        while ($rec = $ilDB->fetchObject($set)) {
            $data = new ilSelfEvaluationData();
            return $data->setObjectValuesFromRecord($data, $rec);
        }

        return null;
    }

    public static function _getInstanceForQuestionId(
        int $dataset_id,
        int $question_id,
        $question_type = ilSelfEvaluationData::QUESTION_TYPE
    ) : ilSelfEvaluationData{

        global $DIC;

        $stmt = $DIC->database()->prepare('SELECT * FROM ' . self::TABLE_NAME .
            ' WHERE dataset_id = ? AND question_id = ? AND question_type = ?;', array('integer', 'integer', 'text'));
        $DIC->database()->execute($stmt, array($dataset_id, $question_id, $question_type));
        while ($rec = $DIC->database()->fetchObject($stmt)) {
            $data = new ilSelfEvaluationData();
            $data->setObjectValuesFromRecord($data, $rec);

            return $data;
        }
        $obj = new self();
        $obj->setQuestionId($question_id);
        $obj->setDatasetId($dataset_id);

        return $obj;
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
     * @param int $dataset_id
     */
    public function setDatasetId($dataset_id)
    {
        $this->dataset_id = $dataset_id;
    }

    /**
     * @return int
     */
    public function getDatasetId()
    {
        return $this->dataset_id;
    }

    /**
     * @param int $question_id
     */
    public function setQuestionId($question_id)
    {
        $this->question_id = $question_id;
    }

    /**
     * @return int
     */
    public function getQuestionId()
    {
        return $this->question_id;
    }

    /**
     * @param string $question_type
     */
    public function setQuestionType($question_type)
    {
        $this->question_type = $question_type;
    }

    /**
     * @return string
     */
    public function getQuestionType()
    {
        return $this->question_type;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = serialize($value);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        $unserialized = unserialize($this->value);
        if ($unserialized !== false) {
            return $unserialized;
        }
        return $this->value;
    }

    /**
     * @param int $creation_date
     */
    public function setCreationDate($creation_date)
    {
        $this->creation_date = $creation_date;
    }

    /**
     * @return int
     */
    public function getCreationDate()
    {
        return $this->creation_date;
    }
}
