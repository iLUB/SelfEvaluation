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
     * @param $parent_ref_id
     * @return ilSelfEvaluationQuestionBlock
     */
    public function cloneTo($parent_id){
        $clone = new self();
        $clone->setParentId($parent_id);
        $clone->setTitle($this->getTitle());
        $clone->setAbbreviation($this->getAbbreviation());
        $clone->setDescription($this->getDescription());
        $clone->setPosition($this->getPosition());
        $clone->update();

        $old_questions = ilSelfEvaluationQuestion::_getAllInstancesForParentId($this->getId());
        foreach ($old_questions as $question){
            $question->cloneTo($clone->getId());
        }

        $old_feedbacks = ilSelfEvaluationFeedback::_getAllInstancesForParentId($this->getId());
        foreach ($old_feedbacks as $feedback){
            $feedback->cloneTo($clone->getId());
        }

        return $clone;
    }

	/**
	 * @param ilSelfEvaluationQuestionBlock $block
	 * @param stdClass                      $rec
	 */
	protected static function setObjectValuesFromRecord(ilSelfEvaluationBlock &$block = NULL, $rec = NULL) {
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


	/**f
	 * @return string
	 */
	public function getAbbreviation() {
		return $this->abbreviation;
	}


	/**
	 * @return string
	 */
	public static function getTableName() {
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