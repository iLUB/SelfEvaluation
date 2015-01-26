<?php
require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('class.ilSelfEvaluationFeedback.php');
/**
 * TableGUI ilSelfEvaluationFeedbackTableGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id:
 *
 */
class ilSelfEvaluationFeedbackTableGUI extends ilTable2GUI {

	/**
	 * @param ilSelfEvaluationFeedbackGUI $a_parent_obj
	 * @param string                      $a_parent_cmd
	 * @param ilSelfEvaluationBlock       $block
	 */
	function __construct(ilSelfEvaluationFeedbackGUI $a_parent_obj, $a_parent_cmd, ilSelfEvaluationBlock $block) {
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
		$this->setTitle($block->getTitle().': '. $this->pl->txt('edit_feedbacks'));
		// Columns
		$this->addColumn($this->pl->txt('fb_title'), 'title', 'auto');
		$this->addColumn($this->pl->txt('fb_body'), 'body', 'auto');
		$this->addColumn($this->pl->txt('fb_start'), 'start', 'auto');
		$this->addColumn($this->pl->txt('fb_end'), 'end', 'auto');
		$this->addColumn($this->pl->txt('actions'), 'asction', 'auto');

        $this->ctrl->setParameter($this->parent_obj, 'feedback_id', NULL);
        $this->addHeaderCommand($this->ctrl->getLinkTarget($a_parent_obj, 'addNew'), $this->pl->txt('add_new_feedback'));

		$this->setRowTemplate($this->pl->getDirectory() . '/templates/default/Feedback/tpl.template_feedback_row.html');
		$this->setData(ilSelfEvaluationFeedback::_getAllInstancesForParentId($a_parent_obj->block->getId(), true));
	}


	/**
	 * @param array $a_set
	 */
	public function fillRow($a_set) {
		$obj = new ilSelfEvaluationFeedback($a_set['id']);
		$this->tpl->setVariable('TITLE', $obj->getTitle());
		$this->tpl->setVariable('BODY', strip_tags($obj->getFeedbackText()));
		$this->tpl->setVariable('START', ($obj->getStartValue() == 0 ? '>= ' : '> ') . $obj->getStartValue() . '%');
		$this->tpl->setVariable('END', '<= ' . $obj->getEndValue() . '%');
		// Actions
		$ac = new ilAdvancedSelectionListGUI();
		$this->ctrl->setParameter($this->parent_obj, 'feedback_id', $obj->getId());
		$ac->setId('fb_' . $obj->getId());
		$ac->addItem($this->pl->txt('edit_feedback'), 'edit_feedback', $this->ctrl->getLinkTarget($this->parent_obj, 'editFeedback'));
		$ac->addItem($this->pl->txt('delete_feedback'), 'delete_question', $this->ctrl->getLinkTarget($this->parent_obj, 'deleteFeedback'));
		$ac->setListTitle($this->pl->txt('actions'));
		//
		$this->tpl->setVariable('ACTIONS', $ac->getHTML());
	}
}

?>