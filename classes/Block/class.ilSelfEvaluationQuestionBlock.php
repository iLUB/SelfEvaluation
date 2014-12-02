<?php
require_once(dirname(__FILE__) . '/../Scale/class.ilSelfEvaluationScale.php');
/**
 * ilSelfEvaluationQuestionBlock
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version
 */
class ilSelfEvaluationQuestionBlock extends ilSelfEvaluationBlock {

	/**
	 * @var ilSelfEvaluationScale
	 */
	protected $scale;
	/**
	 * @var string
	 */
	protected $abbreviation = '';


	public function read() {
		parent::read();
		$this->setScale(ilSelfEvaluationScale::_getInstanceByRefId($this->getParentId()));
	}


	/**
	 * @return array
	 */
	protected function getNonDbFields() {
		return array( 'db', 'scale' );
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
	 * @param string $abbreviation
	 */
	public function setAbbreviation($abbreviation) {
		$this->abbreviation = $abbreviation;
	}


	/**
	 * @return string
	 */
	public function getAbbreviation() {
		return $this->abbreviation;
	}


	/**
	 * @return string
	 */
	public function getTableName() {
		return 'rep_robj_xsev_block';
	}


	//
	// Static
	//
	/**
	 * @param int  $parent_id ilObjSelfEvaluation obj id
	 * @param bool $as_array
	 *
	 * @return ilSelfEvaluationBlock[]
	 */
	public static function _getAllInstancesByParentId($parent_id, $as_array = false) {
		return self::getAllInstancesByParentId($parent_id, $as_array);
	}
}

?>