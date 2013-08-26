<?php
require_once('class.ilObjSelfEvaluationGUI.php');
require_once('class.ilSelfEvaluationBlock.php');
require_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');
/**
 * GUI-Class ilSelfEvaluationAdministrationGUI
 *
 * @author            Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @version           $Id:
 *
 * @ilCtrl_Calls      ilSelfEvaluationAdministrationGUI:  ilObjSelfEvaluationGUI
 * @ilCtrl_IsCalledBy ilSelfEvaluationAdministrationGUI: ilCommonActionDispatcherGUI, ilObjSelfEvaluationGUI
 */
class ilSelfEvaluationAdministrationGUI {

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs_gui;


	/**
	 * @param ilObjSelfEvaluationGUI $parent
	 */
	public function __construct(ilObjSelfEvaluationGUI $parent) {
		global $tpl, $ilCtrl;
		/**
		 * @var $tpl    ilTemplate
		 * @var $ilCtrl ilCtrl
		 */
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->parent = $parent;
		$this->tabs_gui = $this->parent->tabs_gui;
		$this->pl = new ilSelfEvaluationPlugin();
	}


	/**
	 * @return bool
	 */
	public function executeCommand() {
		$cmd = ($this->ctrl->getCmd()) ? $this->ctrl->getCmd() : $this->getStandardCommand();
		switch ($cmd) {
			default:
				$this->performCommand($cmd);
				break;
		}

		return true;
	}


	/**
	 * @return string
	 */
	public function getStandardCommand() {
		return 'showContent';
	}


	/**
	 * @param $cmd
	 */
	function performCommand($cmd) {
		switch ($cmd) {
			case 'editProperties':
				//				$this->checkPermission('write'); FSX
				$this->$cmd();
				break;
			case 'showContent':
				//				$this->checkPermission('read'); FSX
				$this->$cmd();
				break;
		}
	}


	public function showContent() {
		$this->tabs_gui->setTabActive('administration');
		$admin = $this->pl->getTemplate('tpl.admin.html', true, true);
		$pos = 5;
		foreach (ilSelfEvaluationBlock::_getAllInstancesByParentId($this->parent->object->getId()) as $block) {
			$admin->setCurrentBlock('new_block');
			$this->ctrl->setParameterByClass('ilSelfEvaluationBlockGUI', 'position', $pos);
			$admin->setVariable('NEW_BLOCK_HREF', $this->ctrl->getLinkTargetByClass('ilSelfEvaluationBlockGUI', 'addBlock'));
			$admin->parseCurrentBlock();
			$admin->setCurrentBlock('block');
			// Actions
			$ac = new ilAdvancedSelectionListGUI();
			$this->ctrl->setParameterByClass('ilSelfEvaluationBlockGUI', 'block_id', $block->getId());
			$ac->setId('block_' . $block->getId());
			$ac->addItem($this->pl->txt('edit_block'), 'edit_block', $this->ctrl->getLinkTargetByClass('ilSelfEvaluationBlockGUI', 'editBlock'));
			$ac->addItem($this->pl->txt('delete_block'), 'delete_block', $this->ctrl->getLinkTargetByClass('ilSelfEvaluationBlockGUI', 'deleteBlock'));
			$ac->addItem($this->pl->txt('edit_questions'), 'edit_questions', $this->ctrl->getLinkTargetByClass('ilSelfEvaluationBlockGUI', 'editQuestions'));
			$ac->addItem($this->pl->txt('edit_feedback'), 'edit_feedback', $this->ctrl->getLinkTargetByClass('ilSelfEvaluationBlockGUI', 'editFeedbacks'));
			$ac->setListTitle($this->pl->txt('actions'));
			$admin->setVariable('ACTIONS', $ac->getHTML());
			$this->ctrl->setParameterByClass('ilSelfEvaluationBlockGUI', 'block_id', 0);
			// Title & Description
			$admin->setVariable('BLOCK_TITLE', $block->getTitle());
			$admin->setVariable('BLOCK_DESCRIPTION', $block->getDescription());
			$admin->parseCurrentBlock();
			$admin->touchBlock('block_row');
			$pos += 10;
		}
		$admin->setCurrentBlock('new_block_bottom');
		$this->ctrl->setParameterByClass('ilSelfEvaluationBlockGUI', 'position', $pos);
		$admin->setVariable('NEW_BLOCK_HREF', $this->ctrl->getLinkTargetByClass('ilSelfEvaluationBlockGUI', 'addBlock'));
		$admin->parseCurrentBlock();
		$this->tpl->setContent($admin->get());
	}
}

?>