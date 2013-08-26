<?php
require_once('class.ilObjSelfEvaluationGUI.php');
require_once('class.ilSelfEvaluationQuestion.php');
require_once('class.ilSelfEvaluationBlock.php');
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
/**
 * GUI-Class ilSelfEvaluationQuestionGUI
 *
 * @author            Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @version           $Id:
 *
 * @ilCtrl_Calls      ilSelfEvaluationQuestionGUI: ilObjSelfEvaluationGUI
 * @ilCtrl_IsCalledBy ilSelfEvaluationQuestionGUI: ilObjSelfEvaluationGUI
 */
class ilSelfEvaluationQuestionGUI {

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs_gui;
	/**
	 * @var ilPropertyFormGUI
	 */
	protected $form;


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
		$this->pl = new ilSelfEvaluationPlugin();
		$this->block = new ilSelfEvaluationBlock($_GET['block_id']);
		$this->object = new ilSelfEvaluationQuestion($_GET['question_id']);
	}


	public function executeCommand() {
		$cmd = ($this->ctrl->getCmd()) ? $this->ctrl->getCmd() : $this->getStandardCommand();
		$this->ctrl->saveParameter($this, 'block_id');
		$this->ctrl->saveParameter($this, 'question_id');
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
			case 'addQuestion':
			case 'createObject':
				//				$this->checkPermission('write'); FSX
				$this->$cmd();
				break;
			case 'showContent':
				//				$this->checkPermission('read'); FSX
				$this->$cmd();
				break;
		}
	}


	public function addQuestion() {
		$this->tabs_gui->setTabActive('administration');
		$this->initQuestionMenu();
		$this->tpl->setContent($this->form->getHTML());
	}


	public function cancel() {
		$this->ctrl->redirectByClass('ilSelfEvaluationBlockGUI');
	}


	/**
	 * @param string $mode
	 */
	public function initQuestionMenu($mode = 'create') {
		$this->form = new  ilPropertyFormGUI();
		$this->form->setTitle($this->pl->txt($mode . '_question'));
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		$this->form->addCommandButton($mode . 'Object', $this->pl->txt($mode . '_question_button'));
		$this->form->addCommandButton('cancel', $this->pl->txt('cancel'));
		$te = new ilTextInputGUI($this->pl->txt('title'), 'title');
		$te->setRequired(true);
		$this->form->addItem($te);
		$te = new ilTextAreaInputGUI($this->pl->txt('question_body'), 'question_body');
		$te->setRequired(true);
		$te->setUseRte(true);
		$this->form->addItem($te);
	}


	public function createObject() {
		$this->initQuestionMenu();
		if ($this->form->checkInput()) {
			$this->object->setTitle($this->form->getInput('title'));
			$this->object->setQuestionBody($this->form->getInput('question_body'));
			$this->object->setParentId($this->block->getId());
			$this->object->create();
			ilUtil::sendSuccess($this->pl->txt('msg_question_created'));
			$this->cancel();
		}
	}


	public function editBlock() {
		$this->initForm('update');
		$this->setObjectValues();
		$this->tpl->setContent($this->form->getHTML());
	}


	public function setObjectValues() {
		$values['title'] = $this->object->getTitle();
		$values['description'] = $this->object->getDescription();
		$this->form->setValuesByArray($values);
	}


	public function updateObject() {
		$this->initForm();
		$this->form->setValuesByPost();
		if ($this->form->checkInput()) {
			$this->object->setTitle($this->form->getInput('title'));
			$this->object->setDescription($this->form->getInput('description'));
			$this->object->update(false);
			ilUtil::sendSuccess($this->pl->txt('msg_block_created'));
			$this->cancel();
		}
	}


	public function deleteBlock() {
		ilUtil::sendQuestion($this->pl->txt('qst_delete_block'));
		$conf = new ilConfirmationGUI();
		$conf->setFormAction($this->ctrl->getFormAction($this));
		$conf->setCancel($this->pl->txt('cancel'), 'cancel');
		$conf->setConfirm($this->pl->txt('delete_block'), 'deleteObject');
		$conf->addItem('block_id', $this->object->getId(), $this->object->getTitle());
		$this->tpl->setContent($conf->getHTML());
	}


	public function deleteObject() {
		ilUtil::sendSuccess($this->pl->txt('msg_block_deleted'), true);
		$this->object->delete();
		$this->cancel();
	}


	public function editQuestions() {
		$table = new ilSelfEvaluationQuestionTableGUI($this, 'editQuestions', $this->object);
		$this->tpl->setContent($table->getHTML());
	}
}

?>