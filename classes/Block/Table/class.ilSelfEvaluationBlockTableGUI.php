<?php
require_once('./Services/Table/classes/class.ilTable2GUI.php');
/**
 * TableGUI ilSelfEvaluationBlockTableGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id:
 *
 */
class ilSelfEvaluationBlockTableGUI extends ilTable2GUI {

	/**
	 * @param ilObjSelfEvaluationGUI $a_parent_obj
	 * @param string                 $a_parent_cmd
	 */
	function __construct(ilObjSelfEvaluationGUI $a_parent_obj, $a_parent_cmd) {
//		echo ilObject2::_lookupObjectId($_GET['ref_id']);
		global $ilCtrl, $ilTabs;
		/**
		 * @var $ilCtrl ilCtrl
		 * @var $ilTabs ilTabsGUI
		 */
		$this->pl = $a_parent_obj->getPluginObject();
		$this->ctrl = $ilCtrl;
		$this->tabs = $ilTabs;
		$this->setId('');
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setTitle($this->pl->txt('block_table_title'));

		// Columns
		$this->addColumn('', '', '20px');
		$this->addColumn($this->pl->txt('title'), false, 'auto');
		$this->addColumn($this->pl->txt('abbreviation'), false, 'auto');
		$this->addColumn($this->pl->txt('description'), false, 'auto');
		$this->addColumn($this->pl->txt('count_questions'), false, 'auto');
		$this->addColumn($this->pl->txt('count_feedbacks'), false, 'auto');
		$this->addColumn($this->pl->txt('feedback_status'), false, 'auto');
		$this->addColumn($this->pl->txt('actions'), false, 'auto');
		$this->setFormAction($ilCtrl->getFormActionByClass('ilSelfEvaluationListBlocksGUI'));
		$this->addMultiCommand('saveSorting', $this->pl->txt('save_sorting'));
		$this->setRowTemplate($this->pl->getDirectory().'/templates/default/Block/tpl.template_block_row.html');
	}


	/**
	 * @param array $a_set
	 */
	public function fillRow($a_set) {
		$id = $a_set['block_id'];
		// Row
		$this->tpl->setVariable('ID', $a_set['position_id']);
		$this->tpl->setVariable('TITLE', $a_set['title']);
		$this->tpl->setVariable('EDIT_LINK', $a_set['edit_link']);
		$this->tpl->setVariable('ABBREVIATION', $a_set['abbreviation']);
		$this->tpl->setVariable('DESCRIPTION', $a_set['description']);
		if ($a_set['questions_link'] == '') {
			$this->tpl->setCurrentBlock('question_count');
			$this->tpl->setVariable('COUNT_QUESTIONS', $a_set['question_count']);
			$this->tpl->parseCurrentBlock();
		} else {
			$this->tpl->setCurrentBlock('question_count_with_link');
			$this->tpl->setVariable('QUESTIONS_LINK', $a_set['questions_link']);
			$this->tpl->setVariable('COUNT_QUESTIONS', $a_set['question_count']);
			$this->tpl->parseCurrentBlock();
		}
		if ($a_set['feedback_link'] == '') {
			$this->tpl->setCurrentBlock('feedback_count');
			$this->tpl->setVariable('COUNT_FEEDBACKS', $a_set['feedback_count']);
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock('status_img');
			$this->tpl->setVariable('FEEDBACK_STATUS', $a_set['status_img']);
			$this->tpl->parseCurrentBlock();
		} else {
			$this->tpl->setCurrentBlock('feedback_count_with_link');
			$this->tpl->setVariable('COUNT_FEEDBACKS', $a_set['feedback_count']);
            $this->tpl->setVariable('FEEDBACK_LINK', $a_set['feedback_link']);
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock('status_img_with_link');
			$this->tpl->setVariable('FEEDBACK_STATUS', $a_set['status_img']);
			$this->tpl->setVariable('FEEDBACK_LINK', $a_set['feedback_link']);
            $this->tpl->parseCurrentBlock();
        }
		// Actions
		require_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');
		$ac = new ilAdvancedSelectionListGUI();
		$ac->setId($a_set['position_id']);
		$ac->setListTitle($this->pl->txt('actions'));
		/**
		 * @var ilSelfEvaluationTableAction[] $actions
		 */
		$actions = unserialize($a_set['actions']);
		foreach ($actions as $action) {
			$ac->addItem($action->getTitle(), $action->getCmd(), $action->getLink());
		}
		$this->tpl->setVariable('ACTIONS', $ac->getHTML());
	}
}

?>