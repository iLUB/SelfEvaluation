<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once(dirname(__FILE__) . '/../class.ilObjSelfEvaluationGUI.php');
require_once('class.ilSelfEvaluationFeedback.php');
require_once('class.ilSelfEvaluationFeedbackTableGUI.php');
/**
 * GUI-Class ilSelfEvaluationFeedbackGUI
 *
 * @author            Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @version           $Id:
 *
 * @ilCtrl_Calls      ilSelfEvaluationFeedbackGUI:
 * @ilCtrl_IsCalledBy ilSelfEvaluationFeedbackGUI:
 */
class ilSelfEvaluationFeedbackGUI {

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs_gui;
	/**
	 * @var ilPropertyFormGUI
	 */
	protected $form;


	function __construct(ilObjSelfEvaluationGUI $parent) {
		global $tpl, $ilCtrl, $ilToolbar;
		/**
		 * @var $tpl       ilTemplate
		 * @var $ilCtrl    ilCtrl
		 * @var $ilToolbar ilToolbarGUI
		 */
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->parent = $parent;
		$this->toolbar = $ilToolbar;
		$this->tabs_gui = $this->parent->tabs_gui;
		$this->pl = new ilSelfEvaluationPlugin();
		//$this->object = new ilSelfEvaluationFeedback($_GET['fid'] ? $_GET['fid'] : 0);
		$this->block = new ilSelfEvaluationBlock($_GET['block_id']);
	}


	public function executeCommand() {
		$this->tabs_gui->setTabActive('administration');
		$this->ctrl->saveParameter($this, 'block_id');
		$this->ctrl->saveParameterByClass('ilSelfEvaluationBlockGUI', 'block_id');
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
		return 'listObjects';
	}


	/**
	 * @param $cmd
	 */
	function performCommand($cmd) {
		switch ($cmd) {
			case 'listObjects':
			case 'addNew':
			case 'cancel':
			case 'createObject':
			case 'updateObject':
				//				$this->checkPermission('read'); FSX
				$this->$cmd();
				break;
		}
	}


	public function cancel() {
		$this->ctrl->redirect($this);
	}


	public function listObjects() {
		$this->toolbar->addButton('new', $this->ctrl->getLinkTarget($this, 'addNew'));
		$table = new ilSelfEvaluationFeedbackTableGUI($this, 'listObjects');
		$this->tpl->setContent($table->getHTML());
	}


	public function addNew() {
		$this->initForm();
		$this->tpl->setContent($this->form->getHTML());
	}


	public function initForm($mode = 'create') {
		$this->form = new  ilPropertyFormGUI();
		$this->form->setTitle($this->pl->txt($mode . '_feedback_form'));
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		$this->form->addCommandButton($mode . 'Object', $this->pl->txt($mode . '_feedback_button'));
		$this->form->addCommandButton('cancel', $this->pl->txt('cancel'));
		// Title
		$te = new ilTextInputGUI($this->pl->txt('title'), 'title');
		$te->setRequired(true);
		$this->form->addItem($te);
		// Description
		$te = new ilTextInputGUI($this->pl->txt('description'), 'description');
		$this->form->addItem($te);
		// StartValue
		$se = new ilSelectInputGUI($this->pl->txt('start_value'), 'start_value');
		for ($x = 1; $x <= 100; $x ++) {
			$opt[$x] = $x . '%';
		}
		$se->setRequired(true);
		$se->setOptions($opt);
		$this->form->addItem($se);
		// EndValue
		$se = new ilSelectInputGUI($this->pl->txt('end_value'), 'end_value');
		$se->setRequired(true);
		$se->setOptions($opt);
		$this->form->addItem($se);
		// Feedbacktext
		$te = new ilTextAreaInputGUI($this->pl->txt('feedback_text'), 'feedback_text');
		$te->setUseRte(true);
		$te->setRequired(true);
		$this->form->addItem($te);
	}


	public function createObject() {
		$this->initForm();
		if ($this->form->checkInput()) {
			$obj = ilSelfEvaluationFeedback::_getNewInstanceByParentId($this->block->getId());
			$obj->setTitle($this->form->getInput('title'));
			$obj->setDescription($this->form->getInput('description'));
			$obj->setStartValue($this->form->getInput('start_value'));
			$obj->setEndValue($this->form->getInput('end_value'));
			$obj->setFeedbackText($this->form->getInput('feedback_text'));
			$obj->create();
			ilUtil::sendSuccess($this->pl->txt('msg_feedback_created'));
			$this->cancel();
		}
		$this->form->setValuesByPost();
		$this->tpl->setContent($this->form->getHTML());
	}
}

?>