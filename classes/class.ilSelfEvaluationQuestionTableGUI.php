<?php
require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('class.ilSelfEvaluationQuestion.php');
/**
 * TableGUI ilSelfEvaluationQuestionTableGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id:
 *
 */
class ilSelfEvaluationQuestionTableGUI extends ilTable2GUI {

	/**
	 * @param ilSelfEvaluationBlockGUI $a_parent_obj
	 * @param string                   $a_parent_cmd
	 * @param ilSelfEvaluationBlock    $block
	 */
	function __construct(ilSelfEvaluationBlockGUI $a_parent_obj, $a_parent_cmd, ilSelfEvaluationBlock $block) {
		global $ilCtrl, $ilTabs;
		/**
		 * @var $ilCtrl ilCtrl
		 * @var $ilTabs ilTabsGUI
		 */
		$this->pl = new ilSelfEvaluationPlugin();
		//		$this->pl->updateLanguages(); // FSX löschen
		$this->ctrl = $ilCtrl;
		$this->tabs = $ilTabs;
		$this->setId('');
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setTitle($this->pl->txt('title'));
		//
		// Columns
		$this->addColumn($this->pl->txt('title'), 'title', 'auto');
		$this->addColumn($this->pl->txt('question_body'), 'question_body', 'auto');
		$this->addColumn($this->pl->txt('actions'), 'actions', 'auto');
		//
		// ...
		// Header
		$this->ctrl->setParameterByClass('ilSelfEvaluationQuestionGUI', 'block_id', $block->getId());

		$this->addHeaderCommand($this->ctrl->getLinkTargetByClass('ilSelfEvaluationQuestionGUI', 'addQuestion'), $this->pl->txt('add_new_question'));
		//$this->addHeaderCommand($this->ctrl->getLinkTarget($a_parent_obj, 'addTemplateForm'), $this->pl->txt('add_new'));
		//$this->setDefaultOrderField('val_order');
		//$this->setDefaultOrderDirection('asc');
		//$this->setEnableHeader(true);
		//$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		//$this->setEnableTitle(true);
		$this->setRowTemplate('tpl.template_row.html', $this->pl->getDirectory());
		$this->setData(ilSelfEvaluationQuestion::_getAllInstancesForParentId($block->getId(), true));
	}


	/**
	 * @param array $a_set
	 */
	public function fillRow($a_set) {
		$obj = new ilSelfEvaluationQuestion($a_set['id']);
		$this->tpl->setVariable('TITLE', $obj->getTitle());
		$this->tpl->setVariable('BODY', $obj->getQuestionBody());
		//
		// ...
//		$this->tpl->touchBlock('row');
	}
}

?>