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
	}


	/**
	 * @param array $a_set
	 */
	public function fillRow($a_set) {
		$id = $a_set['id'];
		// Row
		$this->tpl->setVariable('ID', $id);
		$this->tpl->setVariable('TITLE', $a_set['title']);
		$this->tpl->setVariable('EDIT_LINK', $a_set['edit_link']);
		$this->tpl->setVariable('ABBREVIATION', $a_set['abbreviation']);
		$this->tpl->setVariable('DESCRIPTION', $a_set['description']);
		$this->tpl->setVariable('QUESTIONS_LINK', $a_set['question_link']);
		$this->tpl->setVariable('FEEDBACK_LINK', $a_set['feedback_link']);
		$this->tpl->setVariable('COUNT_QUESTIONS', count(ilSelfEvaluationQuestion::_getAllInstancesForParentId($id)));
		$this->tpl->setVariable('COUNT_FEEDBACKS', count(ilSelfEvaluationFeedback::_getAllInstancesForParentId($id)));
		$this->tpl->setVariable('FEEDBACK_STATUS', ilSelfEvaluationFeedback::_isComplete($id) ? ilUtil::getImagePath('icon_ok.png') : ilUtil::getImagePath('icon_not_ok.png'));
		// Actions
		$ac = new ilAdvancedSelectionListGUI();
		$ac->setId('block_' . $id);
		$ac->setListTitle($this->pl->txt('actions'));
		foreach ($a_set['actions'] as $action) {
			$ac->addItem($action['title'], $action['value'], $action['link']);
		}
		//
		$this->ctrl->setParameterByClass('ilSelfEvaluationBlockGUI', 'block_id', 0);
		$this->tpl->setVariable('ACTIONS', $ac->getHTML());
	}
}

?>