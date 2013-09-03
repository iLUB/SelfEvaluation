<?php
require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('class.ilSelfEvaluationBlock.php');
require_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');
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
		$this->setTitle($this->pl->txt('block_table_title'));
		//
		// Columns
		$this->addColumn('', 'position', '20px');
		$this->addColumn($this->pl->txt('title'), 'title', 'auto');
		$this->addColumn($this->pl->txt('description'), 'description', 'auto');
		$this->addColumn($this->pl->txt('actions'), 'actions', 'auto');
		$this->addHeaderCommand($this->ctrl->getLinkTargetByClass('ilSelfEvaluationBlockGUI', 'addBlock'), $this->pl->txt('add_new_block'));
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->addMultiCommand('saveSorting', $this->pl->txt('save_sorting'));
		$this->setRowTemplate('tpl.template_block_row.html', $this->pl->getDirectory());
		$this->setData(ilSelfEvaluationBlock::_getAllInstancesByParentId($a_parent_obj->object->getId(), true));
	}


	/**
	 * @param array $a_set
	 */
	public function fillRow($a_set) {
		$obj = new ilSelfEvaluationBlock($a_set['id']);
		$this->tpl->setVariable('ID', $obj->getId());
		$this->tpl->setVariable('TITLE', $obj->getTitle());
		$this->tpl->setVariable('DESCRIPTION', $obj->getDescription());
		// Actions
		$ac = new ilAdvancedSelectionListGUI();
		$this->ctrl->setParameterByClass('ilSelfEvaluationBlockGUI', 'block_id', $obj->getId());
		$this->ctrl->setParameterByClass('ilSelfEvaluationQuestionGUI', 'block_id', $obj->getId());
		$ac->setId('block_' . $obj->getId());
		$ac->addItem($this->pl->txt('edit_block'), 'edit_block', $this->ctrl->getLinkTargetByClass('ilSelfEvaluationBlockGUI', 'editBlock'));
		$ac->addItem($this->pl->txt('delete_block'), 'delete_block', $this->ctrl->getLinkTargetByClass('ilSelfEvaluationBlockGUI', 'deleteBlock'));
		$ac->addItem($this->pl->txt('edit_questions'), 'edit_questions', $this->ctrl->getLinkTargetByClass('ilSelfEvaluationQuestionGUI', 'showContent'));
		$ac->addItem($this->pl->txt('edit_feedback'), 'edit_feedback', $this->ctrl->getLinkTargetByClass('ilSelfEvaluationBlockGUI', 'editFeedbacks'));
		$ac->setListTitle($this->pl->txt('actions'));
		//
		$this->ctrl->setParameterByClass('ilSelfEvaluationBlockGUI', 'block_id', 0);
		$this->tpl->setVariable('ACTIONS', $ac->getHTML());
	}
}

?>