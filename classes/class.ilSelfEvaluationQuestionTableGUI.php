<?php
require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('class.ilSelfEvaluationQuestion.php');
require_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');
/**
 * TableGUI ilSelfEvaluationQuestionTableGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id:
 *
 */
class ilSelfEvaluationQuestionTableGUI extends ilTable2GUI {

	/**
	 * @param ilSelfEvaluationQuestionGUI $a_parent_obj
	 * @param string                      $a_parent_cmd
	 * @param ilSelfEvaluationBlock       $block
	 */
	function __construct(ilSelfEvaluationQuestionGUI $a_parent_obj, $a_parent_cmd, ilSelfEvaluationBlock $block) {
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
		$this->block = $block;
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setTitle($this->pl->txt('question_table_title'));
		//
		// Columns
		if ($this->block->isBlockSortable()) {
			$this->addColumn('', 'position', '20px');
			$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
			$this->addMultiCommand('saveSorting', $this->pl->txt('save_sorting'));
		}
		$this->addColumn($this->pl->txt('title'), 'title', 'auto');
		$this->addColumn($this->pl->txt('question_body'), 'question_body', 'auto');
		$this->addColumn($this->pl->txt('actions'), 'actions', 'auto');
		//
		// ...
		// Header
		$this->ctrl->setParameterByClass('ilSelfEvaluationQuestionGUI', 'block_id', $block->getId());
		$this->addHeaderCommand($this->ctrl->getLinkTargetByClass('ilSelfEvaluationQuestionGUI', 'addQuestion'), $this->pl->txt('add_new_question'));
		$this->setRowTemplate('tpl.template_question_row.html', $this->pl->getDirectory());
		$this->setData(ilSelfEvaluationQuestion::_getAllInstancesForParentId($block->getId(), true));
	}


	/**
	 * @param array $a_set
	 */
	public function fillRow($a_set) {
		$obj = new ilSelfEvaluationQuestion($a_set['id']);
		if ($this->block->isBlockSortable()) {
			$this->tpl->setVariable('ID', $obj->getId());
		}
		$this->tpl->setVariable('TITLE', $obj->getTitle());
		$this->tpl->setVariable('BODY', strip_tags($obj->getQuestionBody()));
		// Actions
		$ac = new ilAdvancedSelectionListGUI();
		$this->ctrl->setParameterByClass('ilSelfEvaluationQuestionGUI', 'question_id', $obj->getId());
		$ac->setId('block_' . $obj->getId());
		$ac->addItem($this->pl->txt('edit_question'), 'edit_question', $this->ctrl->getLinkTargetByClass('ilSelfEvaluationQuestionGUI', 'editQuestion'));
		$ac->addItem($this->pl->txt('delete_question'), 'delete_question', $this->ctrl->getLinkTargetByClass('ilSelfEvaluationQuestionGUI', 'deleteQuestion'));
		$ac->setListTitle($this->pl->txt('actions'));
		//
		$this->tpl->setVariable('ACTIONS', $ac->getHTML());
	}
}

?>