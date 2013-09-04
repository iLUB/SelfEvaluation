<?php
require_once('class.ilObjSelfEvaluationGUI.php');
require_once('class.ilSelfEvaluationQuestion.php');
require_once('class.ilSelfEvaluationBlock.php');
require_once('class.ilSelfEvaluationQuestionTableGUI.php');
require_once('class.ilMatrixFieldInputGUI.php');
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
/**
 * GUI-Class ilSelfEvaluationQuestionGUI
 *
 * @author            Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @version           $Id:
 *
 * @ilCtrl_Calls      ilSelfEvaluationQuestionGUI:
 * @ilCtrl_IsCalledBy ilSelfEvaluationQuestionGUI:
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


	function __construct(ilObjSelfEvaluationGUI $parent, $question_id = 0, $block_id = 0) {
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
		$this->block = new ilSelfEvaluationBlock($block_id ? $block_id : $_GET['block_id']);
		$this->object = new ilSelfEvaluationQuestion($question_id ? $question_id : $_GET['question_id']);
	}


	public function executeCommand() {
		$cmd = ($this->ctrl->getCmd()) ? $this->ctrl->getCmd() : $this->getStandardCommand();
		$this->ctrl->saveParameter($this, 'position');
		$this->ctrl->saveParameter($this, 'block_id');
		$this->ctrl->saveParameter($this, 'question_id');
		$this->tabs_gui->setTabActive('administration');
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
			case 'editQuestion':
			case 'updateObject':
			case 'saveSorting':
			case 'deleteQuestion':
			case 'deleteObject':
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
		$this->initQuestionMForm();
		$this->tpl->setContent($this->form->getHTML());
	}


	public function cancel() {
		$this->ctrl->redirectByClass('ilSelfEvaluationQuestionGUI');
	}


	/**
	 * @param string $mode
	 */
	public function initQuestionMForm($mode = 'create') {
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
		$this->initQuestionMForm();
		if ($this->form->checkInput()) {
			$this->object->setTitle($this->form->getInput('title'));
			$this->object->setQuestionBody($this->form->getInput('question_body'));
			$this->object->setParentId($this->block->getId());
			$this->object->create();
			ilUtil::sendSuccess($this->pl->txt('msg_question_created'));
			$this->cancel();
		}
	}


	public function editQuestion() {
		$this->initQuestionMForm('update');
		$this->setObjectValues();
		$this->tpl->setContent($this->form->getHTML());
	}


	public function setObjectValues() {
		$values['title'] = $this->object->getTitle();
		$values['question_body'] = $this->object->getQuestionBody();
		$this->form->setValuesByArray($values);
	}


	public function updateObject() {
		$this->initQuestionMForm();
		$this->form->setValuesByPost();
		if ($this->form->checkInput()) {
			$this->object->setTitle($this->form->getInput('title'));
			$this->object->setQuestionBody($this->form->getInput('question_body'));
			$this->object->update();
			ilUtil::sendSuccess($this->pl->txt('msg_question_updated'));
			$this->cancel();
		}
	}


	public function deleteQuestion() {
		ilUtil::sendQuestion($this->pl->txt('qst_delete_question'));
		$conf = new ilConfirmationGUI();
		$conf->setFormAction($this->ctrl->getFormAction($this));
		$conf->setCancel($this->pl->txt('cancel'), 'cancel');
		$conf->setConfirm($this->pl->txt('delete_question'), 'deleteObject');
		$conf->addItem('question_id', $this->object->getId(), $this->object->getTitle());
		$this->tpl->setContent($conf->getHTML());
	}


	public function deleteObject() {
		ilUtil::sendSuccess($this->pl->txt('msg_question_deleted'), true);
		$this->object->delete();
		$this->cancel();
	}


	public function showContent() {
		if ($this->block->isBlockSortable()) {
			$this->tpl->addJavaScript($this->pl->getDirectory() . '/templates/sortable.js');
		}
		$table = new ilSelfEvaluationQuestionTableGUI($this, 'showContent', $this->block);
		$this->tpl->setContent($table->getHTML());
	}


	public function saveSorting() {
		foreach ($_POST['position'] as $k => $v) {
			$obj = new ilSelfEvaluationQuestion($v);
			$obj->setPosition($k);
			$obj->update();
		}
		ilUtil::sendSuccess($this->pl->txt('sorting_saved'), true);
		$this->ctrl->redirect($this, 'showContent');
	}


	/**
	 * @param ilPropertyFormGUI $parent_form
	 *
	 * @return ilPropertyFormGUI
	 */
	public function getQuestionForm(ilPropertyFormGUI $parent_form = NULL) {
		if ($parent_form) {
			$form = $parent_form;
		} else {
			$form = new ilPropertyFormGUI();
		}
		$te = new ilMatrixFieldInputGUI($this->object->getTitle(), 'qst_' . '_' . $this->object->getId());
		$te->setScale($this->block->getScale()->getUnitsAsArray());
		$te->setQuestion($this->object->getQuestionBody());
		$te->setRequired(true);
		$form->addItem($te);

		return $form;
	}
}

?>