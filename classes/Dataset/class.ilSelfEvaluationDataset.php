<?php
require_once('class.ilSelfEvaluationData.php');
require_once(dirname(__FILE__) . '/../Question/class.ilSelfEvaluationQuestionGUI.php');
require_once(dirname(__FILE__) . '/../Question/class.ilSelfEvaluationQuestion.php');
require_once(dirname(__FILE__) . '/../Question/class.ilSelfEvaluationMetaQuestionGUI.php');
require_once(dirname(__FILE__) . '/../Feedback/class.ilSelfEvaluationFeedback.php');
require_once(dirname(__FILE__) . '/../Scale/class.ilSelfEvaluationScale.php');
require_once(dirname(__FILE__) . '/../Identity/class.ilSelfEvaluationIdentity.php');
/**
 * ilSelfEvaluationDataset
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version
 */
class ilSelfEvaluationDataset {

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
	 * @param $id
	 */
	function __construct($id = 0) {
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


	public function read() {
		$set = $this->db->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE id = '
			. $this->db->quote($this->getId(), 'integer'));
		while ($rec = $this->db->fetchObject($set)) {
			$this->setObjectValuesFromRecord($this, $rec);
		}
	}


	/**
	 * @return array
	 */
	public function getArrayForDb() {
		$e = array();
		foreach (get_object_vars($this) as $k => $v) {
			if (! in_array($k, array( 'db' ))) {
				$e[$k] = array( self::_getType($v), $this->$k );
			}
		}

		return $e;
	}


	/**
	 * @param ilSelfEvaluationDataset $data_set
	 * @param stdClass                $rec
	 */
	protected function setObjectValuesFromRecord(ilSelfEvaluationDataset &$data_set, $rec) {
		foreach ($this->getArrayForDb() as $k => $v) {
			$data_set->{$k} = $rec->{$k};
		}
	}


	/**
	 * @param string $postvar_key
	 *
	 * @return string|false
	 */
	protected function determineQuestionType($postvar_key) {
		$type = FALSE;

		if (strpos($postvar_key, ilSelfEvaluationQuestionGUI::POSTVAR_PREFIX) === 0) {
			$type = ilSelfEvaluationData::QUESTION_TYPE;
		} else if (strpos($postvar_key, ilSelfEvaluationMetaQuestionGUI::POSTVAR_PREFIX) === 0) {
			$type = ilSelfEvaluationData::META_QUESTION_TYPE;
		}

		return $type;
	}


	/**
	 * @param string $question_type
	 * @param string $postvar_key
	 *
	 * @return int|false
	 */
	protected function getQuestionId($question_type, $postvar_key) {
		$qid = FALSE;

		if ($question_type == ilSelfEvaluationData::QUESTION_TYPE) {
			$qid = (int)str_replace(ilSelfEvaluationQuestionGUI::POSTVAR_PREFIX, '', $postvar_key);
		} else if ($question_type == ilSelfEvaluationData::META_QUESTION_TYPE) {
			$qid = (int)str_replace(ilSelfEvaluationMetaQuestionGUI::POSTVAR_PREFIX, '', $postvar_key);
		}

		return $qid;
	}


	/**
	 * @param int $qid
	 * @param string $question_type
	 *
	 * @return bool
	 */
	protected function questionExists($qid, $question_type) {
		if ($question_type == ilSelfEvaluationData::QUESTION_TYPE) {
			return ilSelfEvaluationQuestion::_isObject($qid);
		} else if ($question_type == ilSelfEvaluationData::META_QUESTION_TYPE) {
			return ilSelfEvaluationMetaQuestion::isObject($qid);
		}

		return FALSE;
	}


	/**
	 * @param $post
	 *
	 * @return array
	 */
	protected function getDataFromPost($post) {
		$data = array();
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
				$data[] = array( 'qid' => $qid, 'value' => $v, 'type' => $type );
			}
		}

		return $data;
	}


	final function initDB() {
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
		if (! $this->db->tableExists(self::TABLE_NAME)) {
			$this->db->createTable(self::TABLE_NAME, $fields);
			$this->db->addPrimaryKey(self::TABLE_NAME, array( 'id' ));
			$this->db->createSequence(self::TABLE_NAME);
		}
	}


	final private function resetDB() {
		$this->db->dropTable(self::TABLE_NAME);
		$this->initDB();
	}


	public function create() {
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
	public function delete() {
		$this->db->manipulate('DELETE FROM ' . ilSelfEvaluationData::TABLE_NAME . ' WHERE dataset_id = '
			. $this->db->quote($this->getId(), 'integer'));

		return $this->db->manipulate('DELETE FROM ' . self::TABLE_NAME . ' WHERE id = '
			. $this->db->quote($this->getId(), 'integer'));
	}


	public function update() {
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


	/**
	 * @param $array array (qid => int, value => string, type => string)
	 */
	public function saveValuesByArray($array) {
		if ($this->getId() == 0) {
			$this->create();
		}

		$qids = [];

		foreach ($array as $item) {
			if(!array_key_exists($item['type'].$item['qid'],$qids))
			{
				$da = new ilSelfEvaluationData();
				$da->setDatasetId($this->getId());
				$da->setQuestionId($item['qid']);
				$da->setValue($item['value']);
				$da->setQuestionType($item['type']);
				$da->setCreationDate(time());
				$da->create();
				$qids[$item['type'].$item['qid']] = true;
			}
		}
	}


    /**
     * @param $post
     */
    public function saveValuesByPost($post) {
		$this->saveValuesByArray($this->getDataFromPost($post));
	}


	/**
	 * @param $array array (qid => int, value => string, type => string)
	 */
	public function updateValuesByArray($array) {
		foreach ($array as $item) {
			$da = ilSelfEvaluationData::_getInstanceForQuestionId($this->getId(), $item['qid'], $item['type']);
			$da->setValue($item['value']);
			$da->update();
		}
	}


	/**
	 * @param $post
	 */
	public function updateValuesByPost($post) {
		$this->updateValuesByArray($this->getDataFromPost($post));
	}


	/**
	 * @param $block_id
	 *
	 * @return mixed
	 */
	public function getDataPerBlock($block_id) { // TODO also fetch meta question data and display it in the feedback
		$sum = array();
		foreach (ilSelfEvaluationQuestion::_getAllInstancesForParentId($block_id) as $qst) {
			$da = ilSelfEvaluationData::_getInstanceForQuestionId($this->getId(), $qst->getId());
			$sum[$qst->getId()] = (int)$da->getValue();
		}

		return $sum;
	}


    /**
     * @return array
     */
	public function getMinPercentageBlock(){
		$min = 100;
        $min_block_id = null;

		$blocks_percentage = $this->getPercentagePerBlock();

		foreach ($blocks_percentage as $block_id => $percentage){
			if($percentage <= $min){
				$min = $percentage;
                $min_block_id = $block_id;
			}
		}
        return ['block'=>$this->getBlockById($min_block_id),'percentage'=>$min];
	}

    /**
     * @return ilSelfEvaluationBlock[]
     */
    public function getMaxPercentageBlock(){
        $max = 0;
        $max_block_id = null;

        $blocks_percentage = $this->getPercentagePerBlock();

        foreach ($blocks_percentage as $block_id => $percentage){
            if($percentage >= $max){
                $max = $percentage;
                $max_block_id = $block_id;
            }
        }

        return ['block'=>$this->getBlockById($max_block_id),'percentage'=>$max];
    }

	/**
	 * @return array
	 * @description return array(block_id => percentage)
	 */
	public function getPercentagePerBlock() {
		$return = array();
		$obj_id = ilSelfEvaluationIdentity::_getObjIdForIdentityId($this->getIdentifierId());
		$scale = ilSelfEvaluationScale::_getInstanceByRefId($obj_id)->getUnitsAsArray();
		$sorted_scale = array_keys($scale);
		sort($sorted_scale);
		$highest = $sorted_scale[count($sorted_scale) - 1];
		foreach (ilSelfEvaluationQuestionBlock::getAllInstancesByParentId($obj_id) as $block) {
			$answer_data = $this->getDataPerBlock($block->getId());
			if (count($answer_data) == 0) {
				continue;
			}
			$answer_total = array_sum($answer_data);
			$anzahl_fragen = count($answer_data);
			$possible_per_block = $anzahl_fragen * $highest;
            if($possible_per_block != 0)
            {
                $percentage = $answer_total / $possible_per_block * 100;
            } else{
                $percentage = 0;
            }

			$return[$block->getId()] = $percentage;
		}

		return $return;
	}

    /**
     * @param $block_id
     * @return ilSelfEvaluationBlock
     */
    public function getBlockById($block_id){
        $obj_id = ilSelfEvaluationIdentity::_getObjIdForIdentityId($this->getIdentifierId());

        foreach (ilSelfEvaluationQuestionBlock::getAllInstancesByParentId($obj_id) as $block) {
            if($block->getId() == $block_id){
            	return $block;
			}
        }
    }


	/**
	 * @return float
	 */
	public function getOverallPercentage() {
		$sum = 0;
		$x = 0;
		foreach ($this->getPercentagePerBlock() as $percentage) {
			$sum += $percentage;
			$x ++;
		}

		if($x == 0){
			return 100;
		}
		return round($sum / $x, 2);
	}


	/**
	 * @param null $a_block_id
	 *
	 * @return ilSelfEvaluationFeedback[]
	 */
	public function getFeedbacksPerBlock($a_block_id = NULL) {
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
	 *
	 * @return ilSelfEvaluationDataset[]
	 */
	public static function _getAllInstancesByIdentifierId($identifier_id) {
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
	 *
	 * @return ilSelfEvaluationDataset[]
	 */
	public static function _getAllInstancesByObjectId($obj_id, $as_array = false) {
		global $ilDB;
		/**
		 * @var $ilDB ilDB
		 */
		$return = array();
		foreach (ilSelfEvaluationIdentity::_getAllInstancesByForForObjId($obj_id) as $identity) {
			$set = $ilDB->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE identifier_id = '
				. $ilDB->quote($identity->getId(), 'integer') . ' ORDER BY creation_date ASC');
			while ($rec = $ilDB->fetchObject($set)) {
				if ($as_array) {
					$return[] = (array)$rec;
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
	 * @param $obj_id
	 *
	 * @return bool
	 */
	public static function _deleteAllInstancesByObjectId($obj_id) {
		foreach (self::_getAllInstancesByObjectId($obj_id) as $obj) {
			$obj->delete();
		}

		return true;
	}


	/**
	 * @param $identifier_id
	 *
	 * @return bool|ilSelfEvaluationDataset
	 */
	public static function _getInstanceByIdentifierId($identifier_id) {
		global $ilDB;
		/**
		 * @var $ilDB ilDB
		 */
		$set = $ilDB->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE identifier_id = '
			. $ilDB->quote($identifier_id, 'integer'));
		while ($rec = $ilDB->fetchObject($set)) {
			$data_set = new ilSelfEvaluationDataset();
			$data_set->setObjectValuesFromRecord($data_set, $rec);
			return $data_set;
		}

		return false;
	}


	/**
	 * @param $identifier_id
	 *
	 * @return ilSelfEvaluationDataset
	 */
	public static function _getNewInstanceForIdentifierId($identifier_id) {
		$obj = new self();
		$obj->setIdentifierId($identifier_id);

		return $obj;
	}


	/**
	 * @param $identifier_id
	 *
	 * @return bool
	 */
	public static function _datasetExists($identifier_id) {
		global $ilDB;
		$set = $ilDB->query('SELECT id FROM ' . self::TABLE_NAME . ' ' . ' WHERE identifier_id = '
			. $ilDB->quote($identifier_id, 'integer'));
		while ($rec = $ilDB->fetchObject($set)) {
			return true;
		}

		return false;
	}


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param int $identifier_id
	 */
	public function setIdentifierId($identifier_id) {
		$this->identifier_id = $identifier_id;
	}


	/**
	 * @return int
	 */
	public function getIdentifierId() {
		return $this->identifier_id;
	}


	/**
	 * @param int $creation_date
	 */
	public function setCreationDate($creation_date) {
		$this->creation_date = $creation_date;
	}


	/**
	 * @return int
	 */
	public function getCreationDate() {
		return $this->creation_date;
	}

    /**
     * @return int
     * @throws Exception
     */
    public function getSubmitDate(){
        $latest_entry = ilSelfEvaluationData::_getLatestInstanceByDatasetId($this->getId());
        if($latest_entry){
            return $latest_entry->getCreationDate();
        } else {
            throw new Exception("Invalid Entry");
        }
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getDuration(){
        $latest_entry = ilSelfEvaluationData::_getLatestInstanceByDatasetId($this->getId());
        if($latest_entry){
            return $latest_entry->getCreationDate()-$this->getCreationDate();
        } else {
            throw new Exception("Invalid Entry");
        }
    }

	//
	// Helper
	//
	/**
	 * @param $var
	 *
	 * @return string
	 */
	public static function _getType($var) {
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
