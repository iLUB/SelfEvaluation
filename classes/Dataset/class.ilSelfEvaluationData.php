<?php

/**
 * ilSelfEvaluationData
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version
 */
class ilSelfEvaluationData
{

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

    /**
     * @param $data
     * @param $rec
     * @return $this
     */
    protected function setObjectValuesFromRecord($data, $rec)
    {
        foreach ($data->getArrayForDb() as $k => $v) {
            $data->{$k} = $rec->{$k};
        }
        return $this;
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

    /**
     * @return bool
     */
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

    /**
     * @param int    $dataset_id
     * @param int    $question_id
     * @param string $question_type
     * @return ilSelfEvaluationData
     */
    public static function _getInstanceForQuestionId(
        $dataset_id,
        $question_id,
        $question_type = ilSelfEvaluationData::QUESTION_TYPE
    ) {

        global $ilDB;
        $stmt = $ilDB->prepare('SELECT * FROM ' . self::TABLE_NAME .
            ' WHERE dataset_id = ? AND question_id = ? AND question_type = ?;', array('integer', 'integer', 'text'));
        $ilDB->execute($stmt, array($dataset_id, $question_id, $question_type));
        while ($rec = $ilDB->fetchObject($stmt)) {
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
