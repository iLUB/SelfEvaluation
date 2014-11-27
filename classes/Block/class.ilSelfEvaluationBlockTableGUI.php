<?php
require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');
require_once('class.ilSelfEvaluationBlock.php');
require_once(dirname(__FILE__) . '/../Question/class.ilSelfEvaluationQuestion.php');
require_once(dirname(__FILE__) . '/../Feedback/class.ilSelfEvaluationFeedback.php');
require_once(dirname(__FILE__) . '/../Form/class.ilOverlayRequestGUI.php');
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
		$this->pl = new ilSelfEvaluationPlugin();
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
		$this->ctrl->setParameterByClass('ilSelfEvaluationBlockGUI', 'block_id', NULL);
		$this->addHeaderCommand(ilOverlayRequestGUI::getLink($this->ctrl->getLinkTargetByClass('ilSelfEvaluationBlockGUI', 'addBlock')), $this->pl->txt('add_new_block'));
		$this->setFormAction($ilCtrl->getFormActionByClass('ilSelfEvaluationBlockGUI'));
		$this->addMultiCommand('saveSorting', $this->pl->txt('save_sorting'));
		$this->setRowTemplate($this->pl->getDirectory().'/templates/default/Block/tpl.template_block_row.html');
		$this->setData(ilSelfEvaluationBlock::_getAllInstancesByParentId($a_parent_obj->object->getId(), true));
	}


	/**
	 * @param array $a_set
	 */
	public function fillRow($a_set) {
		$obj = new ilSelfEvaluationBlock($a_set['id']);
		$this->ctrl->setParameterByClass('ilSelfEvaluationBlockGUI', 'block_id', $obj->getId());
		$this->ctrl->setParameterByClass('ilSelfEvaluationQuestionGUI', 'block_id', $obj->getId());
		$this->ctrl->setParameterByClass('ilSelfEvaluationFeedbackGUI', 'block_id', $obj->getId());
		// Row
		$this->tpl->setVariable('ID', $obj->getId());
		$this->tpl->setVariable('TITLE', $obj->getTitle());
		$this->tpl->setVariable('EDIT_LINK', ilOverlayRequestGUI::getLink($this->ctrl->getLinkTargetByClass('ilSelfEvaluationBlockGUI', 'editBlock')));
		$this->tpl->setVariable('ABBREVIATION', $obj->getAbbreviation());
		$this->tpl->setVariable('DESCRIPTION', $obj->getDescription());
		$this->tpl->setVariable('QUESTIONS_LINK', $this->ctrl->getLinkTargetByClass('ilSelfEvaluationQuestionGUI', 'showContent'));
		$this->tpl->setVariable('FEEDBACK_LINK', $this->ctrl->getLinkTargetByClass('ilSelfEvaluationFeedbackGUI', 'listObjects'));
		$this->tpl->setVariable('COUNT_QUESTIONS', count(ilSelfEvaluationQuestion::_getAllInstancesForParentId($obj->getId())));
		$this->tpl->setVariable('COUNT_FEEDBACKS', count(ilSelfEvaluationFeedback::_getAllInstancesForParentId($obj->getId())));
		$this->tpl->setVariable('FEEDBACK_STATUS', ilSelfEvaluationFeedback::_isComplete($obj->getId()) ? ilUtil::getImagePath('icon_ok.png') : ilUtil::getImagePath('icon_not_ok.png'));
		// Actions
		$ac = new ilAdvancedSelectionListGUI();
		$ac->setId('block_' . $obj->getId());
		$ac->addItem($this->pl->txt('edit_block'), 'edit_block', ilOverlayRequestGUI::getLink($this->ctrl->getLinkTargetByClass('ilSelfEvaluationBlockGUI', 'editBlock')));
		$ac->addItem($this->pl->txt('delete_block'), 'delete_block', ilOverlayRequestGUI::getLink($this->ctrl->getLinkTargetByClass('ilSelfEvaluationBlockGUI', 'deleteBlock')));
		$ac->addItem($this->pl->txt('edit_questions'), 'edit_questions', $this->ctrl->getLinkTargetByClass('ilSelfEvaluationQuestionGUI', 'showContent'));
		$ac->addItem($this->pl->txt('edit_feedback'), 'edit_feedbacks', $this->ctrl->getLinkTargetByClass('ilSelfEvaluationFeedbackGUI'));
		$ac->setListTitle($this->pl->txt('actions'));
		//
		$this->ctrl->setParameterByClass('ilSelfEvaluationBlockGUI', 'block_id', 0);
		$this->tpl->setVariable('ACTIONS', $ac->getHTML());
	}
}

?>