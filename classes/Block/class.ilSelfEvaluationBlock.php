<?php
require_once(dirname(__FILE__) . '/../Scale/class.ilSelfEvaluationScale.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/srModelObject/class.srModelObject.php');
/**
 * ilSelfEvaluationBlock
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.0
 */
class ilSelfEvaluationBlock extends srModelObject {

	/**
	 * @return string
	 */
	static function returnDbTableName() {
		return 'rep_robj_xsev_block';
	}


	/**
	 * @var int
	 */
	public $id = 0;
	/**
	 * @var string
	 *
	 * @db_hasfield  true
	 * @db_fieldtype text
	 * @db_length    1024
	 */
	protected $title = '';
	/**
	 * @var string
	 *
	 * @db_hasfield  true
	 * @db_fieldtype text
	 * @db_length    1024
	 */
	protected $description = '';
	/**
	 * @var string
	 *
	 * @db_hasfield  true
	 * @db_fieldtype integer
	 * @db_length    2
	 */
	protected $position = 99;
	/**
	 * @var string
	 *
	 * @db_hasfield  true
	 * @db_fieldtype integer
	 * @db_length    8
	 */
	protected $parent_id = 0;
	/**
	 * @var ilSelfEvaluationScale
	 */
	protected $scale;


	/**
	 * @return bool
	 */
	public function isBlockSortable() {
		/**
		 * @var $parentObject ilObjSelfEvaluation
		 */
		$parentObject = ilObjectFactory::getInstanceByObjId($this->getParentId());
		switch ($parentObject->getSortType()) {
			case ilObjSelfEvaluation::SORT_MANUALLY:
				return true;
			case ilObjSelfEvaluation::SORT_SHUFFLE:
				return false;
		}
	}


	//
	// Static
	//
	/**
	 * @param      $parent_id
	 * @param bool $as_array
	 *
	 * @return ilSelfEvaluationBlock[]
	 */
	public static function _getAllInstancesByParentId($parent_id, $as_array = false) {
		$return = self::where('parent_id = ' . $parent_id);
		if ($as_array) {
			return $return->getArray();
		} else {
			return $return->get();
		}
	}


	/**
	 * @param $parent_id
	 *
	 * @return int
	 */
	public static function _getNextPosition($parent_id) {
		global $ilDB;
		$set = $ilDB->query('SELECT MAX(position) next_pos FROM ' . self::returnDbTableName() . ' '
			. ' WHERE parent_id = ' . $ilDB->quote($parent_id, 'integer'));
		while ($rec = $ilDB->fetchObject($set)) {
			return $rec->next_pos + 1;
		}

		return 1;
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
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}


	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}


	/**
	 * @param int $parent_id
	 */
	public function setParentId($parent_id) {
		$this->parent_id = $parent_id;
	}


	/**
	 * @return int
	 */
	public function getParentId() {
		return $this->parent_id;
	}


	/**
	 * @param int $position
	 */
	public function setPosition($position) {
		$this->position = $position;
	}


	/**
	 * @return int
	 */
	public function getPosition() {
		return $this->position;
	}


	/**
	 * @param \ilSelfEvaluationScale $scale
	 */
	public function setScale($scale) {
		$this->scale = $scale;
	}


	/**
	 * @return \ilSelfEvaluationScale
	 */
	public function getScale() {
		return $this->scale;
	}


	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}


	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
}

?>