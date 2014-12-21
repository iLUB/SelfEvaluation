<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
require_once('./Modules/SurveyQuestionPool/classes/class.ilMatrixRowWizardInputGUI.php');
require_once(dirname(__FILE__) . '/../class.ilObjSelfEvaluationGUI.php');
require_once('class.ilSelfEvaluationBlock.php');
require_once('class.ilSelfEvaluationBlockTableGUI.php');
require_once(dirname(__FILE__) . '/../Question/class.ilSelfEvaluationQuestionTableGUI.php');
require_once(dirname(__FILE__) . '/../Question/class.ilSelfEvaluationQuestion.php');
require_once(dirname(__FILE__) . '/../Question/class.ilSelfEvaluationQuestionGUI.php');
require_once(dirname(__FILE__) . '/../Form/class.ilMatrixHeaderGUI.php');
require_once(dirname(__FILE__) . '/../Form/class.ilOverlayRequestGUI.php');
require_once(dirname(__FILE__) . '/../Form/class.ilFormSectionHeaderGUIFixed.php');
/**
 * GUI-Class ilSelfEvaluationBlockGUI
 *
 * @author            Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @version           $Id:
 *
 * @ilCtrl_Calls      ilSelfEvaluationBlockGUI:
 * @ilCtrl_IsCalledBy ilSelfEvaluationBlockGUI:
 */
class ilSelfEvaluationBlockGUI {

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs_gui;
	/**
	 * @var ilSelfEvaluationBlock
	 */
	public $object;
	/**
	 * @var ilPropertyFormGUI
	 */
	protected $form;


	function __construct(ilObjSelfEvaluationGUI $parent, $block_id = 0) {
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
		$this->object = new ilSelfEvaluationBlock($block_id ? $block_id : $_GET['block_id']);
	}


	public function executeCommand() {
		$cmd = ($this->ctrl->getCmd()) ? $this->ctrl->getCmd() : $this->getStandardCommand();
		$this->ctrl->saveParameter($this, 'block_id');
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
		return 'addNew';
	}


	/**
	 * @param $cmd
	 */
	function performCommand($cmd) {
		switch ($cmd) {
			case 'addBlock':
			case 'createObject':
			case 'editBlock':
			case 'updateObject':
			case 'deleteBlock':
			case 'deleteObject':
			case 'editQuestions':
			case 'showContent':
			case 'saveSorting':
				$this->parent->permissionCheck('write');
				$this->$cmd();
				break;
			case 'cancel':
				$this->parent->permissionCheck('read');
				$this->$cmd();
				break;
		}
	}


	public function addBlock() {
		$this->initForm();
		$this->tpl->setContent($this->form->getHTML());
	}


	public function cancel() {
		$this->ctrl->redirectByClass('ilSelfEvaluationBlockGUI', 'showContent');
	}


	public function initForm($mode = 'create') {
		$this->form = new  ilPropertyFormGUI();
		$this->form->setTitle($this->pl->txt($mode . '_block'));
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		$this->form->addCommandButton($mode . 'Object', $this->pl->txt($mode . '_block_button'));
		$this->form->addCommandButton('cancel', $this->pl->txt('cancel'));
		$te = new ilTextInputGUI($this->pl->txt('title'), 'title');
		$te->setRequired(true);
		$this->form->addItem($te);
		$te = new ilTextAreaInputGUI($this->pl->txt('description'), 'description');
		$this->form->addItem($te);
		if (ilObjSelfEvaluationGUI::DEV) {
			$te = new ilColorPickerInputGUI($this->pl->txt('color'), 'color');
			$te->setDefaultColor('000000');
			$this->form->addItem($te);
		}
	}


	public function createObject() {
		$this->initForm();
		if ($this->form->checkInput()) {
			$this->object->setTitle($this->form->getInput('title'));
			$this->object->setDescription($this->form->getInput('description'));
			$this->object->setParentId($this->parent->object->getId());
			$this->object->create();
			ilUtil::sendSuccess($this->pl->txt('msg_block_created'));
			$this->cancel();
		}
		$this->tpl->setContent($this->form->getHTML());
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
			ilUtil::sendSuccess($this->pl->txt('msg_block_updated'));
			$this->cancel();
		}
		$this->tpl->setContent($this->form->getHTML());
	}


	public function deleteBlock() {
		//		ilUtil::sendQuestion($this->pl->txt('qst_delete_block'));
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


	/**
	 * @param ilPropertyFormGUI $parent_form
	 *
	 * @return ilPropertyFormGUI
	 */
	public function getBlockForm(ilPropertyFormGUI $parent_form = NULL, $first = true) {
		if ($parent_form) {
			$form = $parent_form;
		} else {
			$form = new ilPropertyFormGUI();
		}
		$h = new ilFormSectionHeaderGUIFixed();
        $h->setTitle($this->object->getTitle());
        if($first)
        {
            $form->addItem($h);
            $h = new ilFormSectionHeaderGUIFixed();
        }
        $h->setInfo($this->object->getDescription());
        $form->addItem($h);
		$sc = new ilMatrixHeaderGUI();
		$sc->setScale($this->object->getScale()->getUnitsAsArray());
		$form->addItem($sc);
		$questions = ilSelfEvaluationQuestion::_getAllInstancesForParentId($this->object->getId());
		if ($this->parent->object->getSortType() == ilObjSelfEvaluation::SORT_SHUFFLE) {
			shuffle($questions);
		}
		foreach ($questions as $qst) {
			$qst_gui = new ilSelfEvaluationQuestionGUI($this->parent, $qst->getId(), $this->object->getId());
			$qst_gui->getQuestionForm($form);
		}

		return $form;
	}


	public function showContent() {
		$this->tpl->addJavaScript($this->pl->getDirectory() . '/templates/js/sortable.js');
		$async = new ilOverlayRequestGUI();
		$async->setAddNewLink($this->ctrl->getLinkTarget($this, 'addBlock'));
		$table = new ilSelfEvaluationBlockTableGUI($this->parent, 'showContent');
		$this->tpl->setContent($async->getHTML() . $table->getHTML());
	}


	public function saveSorting() {
		foreach ($_POST['position'] as $k => $v) {
			$obj = new ilSelfEvaluationBlock($v);
			$obj->setPosition($k + 1);
			$obj->update();
		}
		ilUtil::sendSuccess($this->pl->txt('sorting_saved'), true);
		$this->ctrl->redirect($this, 'showContent');
	}
}

?>