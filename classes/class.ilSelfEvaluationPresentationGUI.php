<?php
require_once('class.ilObjSelfEvaluationGUI.php');
require_once('class.ilSelfEvaluationBlock.php');
require_once('class.ilSelfEvaluationBlockGUI.php');
/**
 * GUI-Class ilSelfEvaluationPresentationGUI
 *
 * @author            Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @version           $Id:
 *
 * @ilCtrl_Calls      ilSelfEvaluationPresentationGUI: ilObjSelfEvaluationGUI
 * @ilCtrl_IsCalledBy ilSelfEvaluationPresentationGUI: ilCommonActionDispatcherGUI, ilObjSelfEvaluationGUI
 */
class ilSelfEvaluationPresentationGUI {

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs_gui;


	function __construct(ilObjSelfEvaluationGUI $parent) {
		global $tpl, $ilCtrl;
		/**
		 * @var $tpl    ilTemplate
		 * @var $ilCtrl ilCtrl
		 */
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->parent = $parent;
		$this->tabs_gui = $this->parent->tabs_gui;
	}


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
		$this->tabs_gui->setTabActive('content');
		$html = '';
		foreach (ilSelfEvaluationBlock::_getAllInstancesByParentId($this->parent->object->getId()) as $block) {
			$block_gui = new ilSelfEvaluationBlockGUI($this->parent, $block->getId());
			$html .= $block_gui->getBLockHTML();
		}
		$this->tpl->setContent($html);
	}
}

?>