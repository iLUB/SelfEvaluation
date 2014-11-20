<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
require_once(dirname(__FILE__) . '/../class.ilObjSelfEvaluationGUI.php');
require_once('class.ilSelfEvaluationQuestion.php');
require_once('class.ilSelfEvaluationQuestionTableGUI.php');
require_once(dirname(__FILE__) . '/../Block/class.ilSelfEvaluationBlock.php');
require_once(dirname(__FILE__) . '/../Form/class.ilMatrixFieldInputGUI.php');
require_once(dirname(__FILE__) . '/../Form/class.ilOverlayRequestGUI.php');
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

	const POSTVAR_PREFIX = 'qst_';
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs_gui;
	/**
	 * @var ilPropertyFormGUI
	 */
	protected $form;


	function __construct(ilObjSelfEvaluationGUI $parent, $question_id = 0, $block_id = 0) {
		global $tpl, $ilCtrl, $ilToolbar;
		/**
		 * @var $tpl       ilTemplate
		 * @var $ilCtrl    ilCtrl
		 * @var $ilToolbar ilToolbarGUI
		 */
		$this->tpl = $tpl;
		$this->toolbar = $ilToolbar;
		$this->ctrl = $ilCtrl;
		$this->parent = $parent;
		$this->tabs_gui = $this->parent->tabs_gui;
		$this->pl = new ilSelfEvaluationPlugin();
		$this->block = new ilSelfEvaluationBlock($block_id ? $block_id : $_GET['block_id']);
		$this->object = new ilSelfEvaluationQuestion($question_id ? $question_id : $_GET['question_id']);
	}


	/**
	 * @return bool
	 */
	public function executeCommand() {
		$cmd = ($this->ctrl->getCmd()) ? $this->ctrl->getCmd() : $this->getStandardCommand();
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
			case 'cancel':
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
		$this->initQuestionForm();
		$this->tpl->setContent($this->form->getHTML());
	}


	public function cancel() {
		$this->ctrl->redirectByClass('ilSelfEvaluationQuestionGUI');
	}


	/**
	 * @param string $mode
	 */
	public function initQuestionForm($mode = 'create') {
		$this->form = new  ilPropertyFormGUI();
		$this->form->setTitle($this->pl->txt($mode . '_question'));
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		$this->form->addCommandButton($mode . 'Object', $this->pl->txt($mode . '_question_button'));
		$this->form->addCommandButton('cancel', $this->pl->txt('cancel'));
		$te = new ilTextInputGUI($this->pl->txt('title'), 'title');
		$te->setRequired(false);
		$this->form->addItem($te);
		require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Form/class.ilTinyMceTextAreaInputGUI.php');
		$te = new ilTinyMceTextAreaInputGUI($this->parent->object, $this->pl->txt('question_body'), 'question_body');
		$te->setRequired(true);
		$cb = new ilCheckboxInputGUI($this->pl->txt('is_inverse'), 'is_inverse');
		$cb->setValue(1);
		$this->form->addItem($cb);
		$this->form->addItem($te);
	}


	public function createObject() {
		$this->initQuestionForm();
		if ($this->form->checkInput()) {
			$this->object->setTitle($this->form->getInput('title'));
			$this->object->setQuestionBody($this->form->getInput('question_body'));
			$this->object->setIsInverse($this->form->getInput('is_inverse'));
			$this->object->setParentId($this->block->getId());
			$this->object->create();
			ilUtil::sendSuccess($this->pl->txt('msg_question_created'));
			$this->cancel();
		}
		$this->tpl->setContent($this->form->getHTML());
	}


	public function editQuestion() {
		$this->initQuestionForm('update');
		$this->setObjectValues();
		$this->tpl->setContent($this->form->getHTML());
	}


	public function setObjectValues() {
		$values['title'] = $this->object->getTitle();
		$values['question_body'] = $this->object->getQuestionBody();
		$values['is_inverse'] = $this->object->getIsInverse();
		$this->form->setValuesByArray($values);
	}


	public function updateObject() {
		$this->initQuestionForm();
		$this->form->setValuesByPost();
		if ($this->form->checkInput()) {
			$this->object->setTitle($this->form->getInput('title'));
			$this->object->setQuestionBody($this->form->getInput('question_body'));
			$this->object->setIsInverse($this->form->getInput('is_inverse'));
			$this->object->update();
			ilUtil::sendSuccess($this->pl->txt('msg_question_updated'));
			$this->cancel();
		}
		$this->tpl->setContent($this->form->getHTML());
	}


	public function deleteQuestion() {
//		ilUtil::sendQuestion($this->pl->txt('qst_delete_question'));
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
		$async = new ilOverlayRequestGUI();
		$async->setAddNewLink($this->ctrl->getLinkTarget($this, 'addQuestion'));
		$this->toolbar->addButton('<b>&lt;&lt; '.$this->pl->txt('back_to_blocks').'</b>', $this->ctrl->getLinkTargetByClass('ilSelfEvaluationBlockGUI', 'showContent'));
		$table = new ilSelfEvaluationQuestionTableGUI($this, 'showContent', $this->block);
		$this->tpl->setContent($async->getHTML() . $table->getHTML());
	}


	public function saveSorting() {
		foreach ($_POST['position'] as $k => $v) {
			$obj = new ilSelfEvaluationQuestion($v);
			$obj->setPosition($k + 1);
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
		$te = new ilMatrixFieldInputGUI($this->object->getTitle(), self::POSTVAR_PREFIX . $this->object->getId());
		$te->setScale($this->block->getScale()->getUnitsAsArray($this->object->getIsInverse()));
		$te->setQuestion($this->object->getQuestionBody());
		$te->setRequired(true);
		$form->addItem($te);

		return $form;
	}


	/**
	 * @param ilObjSelfEvaluationGUI $parent
	 * @param ilPropertyFormGUI      $form
	 *
	 * @return ilPropertyFormGUI
	 */
	public static function getAllQuestionsForms(ilObjSelfEvaluationGUI $parent, ilPropertyFormGUI &$form) {
		$parent_id = $parent->object->getId();
		/** @var ilSelfEvaluationQuestion[] $questions */
		$questions = array();

		foreach (ilSelfEvaluationBlock::_getAllInstancesByParentId($parent_id) as $block) {
			foreach (ilSelfEvaluationQuestion::_getAllInstancesForParentId($block->getId()) as $qst) {
				$questions[] = $qst;
			}
		}
		$sc = new ilMatrixHeaderGUI();
		$sc->setScale($block->getScale()->getUnitsAsArray()); // TODO this looks nasty. Why is the the scale of hte last block onyl?
		$form->addItem($sc);
		shuffle($questions);
		foreach ($questions as $qst) {
			$qst_form = new self($parent, $qst->getId(), $block->getId());
			$form = $qst_form->getQuestionForm($form);
		}

		return $form;
	}
}

?>