<?php
require_once(dirname(__FILE__) . '/class.ilSelfEvaluationBlock.php');
require_once(dirname(__FILE__) . '/../Scale/class.ilSelfEvaluationScale.php');
require_once('int.ilSelfEvaluationQuestionBlockInterface.php');

/**
 * Class ilSelfEvaluationQuestionBlock
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class ilSelfEvaluationQuestionBlock extends ilSelfEvaluationBlock implements ilSelfEvaluationQuestionBlockInterface {
	/**
	 * @var string
	 */
	protected $abbreviation = '';

	/**
	 * @param ilSelfEvaluationQuestionBlock $block
	 * @param stdClass                      $rec
	 */
	protected function setObjectValuesFromRecord(ilSelfEvaluationQuestionBlock &$block, $rec) {
		parent::setObjectValuesFromRecord($block, $rec);
	}


	/**
	 * @return array
	 */
	protected function getNonDbFields() {
		return array_merge(parent::getNonDbFields(), array('scale'));
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

	/**
	 * @return ilSelfEvaluationBlockTableRow
	 */
	public function getBlockTableRow() {
		require_once(dirname(__FILE__) . '/Table/class.ilSelfEvaluationQuestionBlockTableRow.php');
		$row = new ilSelfEvaluationQuestionBlockTableRow($this);

		return $row;
	}

    /**
     * @return ilSelfEvaluationQuestion[]
     */
    public function getQuestions(){
        return(ilSelfEvaluationQuestion::_getAllInstancesForParentId($this->getId()));
    }
}

?>