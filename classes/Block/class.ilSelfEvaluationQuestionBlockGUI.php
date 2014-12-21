<?php
require_once(dirname(__FILE__) . '/class.ilSelfEvaluationBlockGUI.php');

/**
 * GUI-Class ilSelfEvaluationQuestionBlockGUI
 *
 * @ilCtrl_isCalledBy ilSelfEvaluationQuestionBlockGUI: ilObjSelfEvaluationGUI
 *
 * @author            Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @author            Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version           $Id:
 */
class ilSelfEvaluationQuestionBlockGUI extends ilSelfEvaluationBlockGUI {


	/**
	 * @var ilSelfEvaluationQuestionBlock
	 */
	public $object;


	public function initForm($mode = 'create') {
		parent::initForm($mode);

		$te = new ilTextInputGUI($this->pl->txt('abbreviation'), 'abbreviation');
		$te->setMaxLength(8);
		$this->form->addItem($te);
	}


	protected function setObjectValuesByPost() {
		parent::setObjectValuesByPost();
		$this->object->setAbbreviation($this->form->getInput('abbreviation'));
	}


	/**
	 * @return array (postvar => value) is set to the form
	 */
	protected function getObjectValuesAsArray() {
		$values = array('abbreviation' => $this->object->getAbbreviation());

		return array_merge(parent::getObjectValuesAsArray(), $values);
	}



	/**
	 * @param ilPropertyFormGUI $parent_form
	 * @param bool              $first
	 *
	 * @return ilPropertyFormGUI
	 */
	public function getBlockForm(ilPropertyFormGUI $parent_form = NULL, $first = true) {
		$form = parent::getBlockForm($parent_form, $first);

		require_once(dirname(__FILE__) . '/../Form/class.ilMatrixHeaderGUI.php');
		$sc = new ilMatrixHeaderGUI();
		$sc->setScale($this->object->getScale()->getUnitsAsArray());
		$form->addItem($sc);

		$questions = ilSelfEvaluationQuestion::_getAllInstancesForParentId($this->object->getId());
		if ($this->parent->object->getSortType() == ilObjSelfEvaluation::SHUFFLE_IN_BLOCKS) {
			shuffle($questions);
		}
		foreach ($questions as $qst) {
			$qst_gui = new ilSelfEvaluationQuestionGUI($this->parent, $qst->getId(), $this->object->getId());
			$qst_gui->getQuestionForm($form);
		}

		return $form;
	}
}

?>