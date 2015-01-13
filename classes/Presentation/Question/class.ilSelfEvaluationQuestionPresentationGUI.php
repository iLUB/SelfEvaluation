<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once(dirname(__FILE__) . '/../../Question/class.ilSelfEvaluationQuestion.php');
require_once(dirname(__FILE__) . '/../../Block/class.ilSelfEvaluationQuestionBlock.php');
require_once('class.ilMatrixFieldInputGUI.php');

/**
 * GUI-Class ilSelfEvaluationQuestionPresentationGUI
 *
 * @author       Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id:
 */
class ilSelfEvaluationQuestionPresentationGUI {

	function __construct($question_id = 0) {
		$this->object = new ilSelfEvaluationQuestion($question_id);
        $this->block = new ilSelfEvaluationQuestionBlock($this->object->getParentId());
	}

	/**
	 * @param ilPropertyFormGUI $parent_form
	 *
	 * @return ilPropertyFormGUI
	 */
	public function getQuestionForm(ilPropertyFormGUI $parent_form = NULL) {
		if ($parent_form) {
			$form = $parent_form;
		} else {
			$form = new ilPropertyFormGUI();
		}
		$te = new ilMatrixFieldInputGUI($this->object->getQuestionBody(), ilSelfEvaluationQuestionGUI::POSTVAR_PREFIX . $this->object->getId());
		$te->setScale(ilSelfEvaluationScale::_getInstanceByRefId($this->block->getParentId())->getUnitsAsArray());
		$te->setRequired(true);
		$form->addItem($te);

		return $form;
	}
}

?>