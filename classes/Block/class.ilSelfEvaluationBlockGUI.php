<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
require_once(dirname(__FILE__) . '/../Form/class.ilFormSectionHeaderGUIFixed.php');


/**
 * GUI-Class ilSelfEvaluationBlockGUI
 *
 * @ilCtrl_isCalledBy ilSelfEvaluationBlockGUI: ilObjSelfEvaluationGUI
 *
 * @author            Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @author            Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version           $Id:
 */
abstract class ilSelfEvaluationBlockGUI {

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


	/**
	 * @param ilObjSelfEvaluationGUI $parent
	 * @param ilSelfEvaluationBlock  $block
	 */
	function __construct(ilObjSelfEvaluationGUI $parent, ilSelfEvaluationBlock $block) {
		global $tpl, $ilCtrl;
		/**
		 * @var $tpl    ilTemplate
		 * @var $ilCtrl ilCtrl
		 */
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->parent = $parent;
		$this->tabs_gui = $this->parent->tabs_gui;
		$this->object = $block;
		$this->pl = new ilSelfEvaluationPlugin();
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
		return 'addBlock';
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
		$this->ctrl->redirectByClass('ilSelfEvaluationListBlocksGUI', 'showContent');
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
	}


	public function createObject() {
		$this->initForm();
		if ($this->form->checkInput()) {
			$this->setObjectValuesByPost();
			$this->object->create();
			ilUtil::sendSuccess($this->pl->txt('msg_block_created'));
			$this->cancel();
		}
		$this->tpl->setContent($this->form->getHTML());
	}


	public function editBlock() {
		$this->initForm('update');
		$values = $this->getObjectValuesAsArray();
		$this->form->setValuesByArray($values);
		$this->tpl->setContent($this->form->getHTML());
	}


	protected function getObjectValuesAsArray() {
		$values['title'] = $this->object->getTitle();
		$values['description'] = $this->object->getDescription();

		return $values;
	}


	public function updateObject() {
		$this->initForm();
		$this->form->setValuesByPost();
		if ($this->form->checkInput()) {
			$this->setObjectValuesByPost();
			$this->object->update();
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
	 * @param bool              $first
	 *
	 * @return ilPropertyFormGUI
	 */
	public function getBlockForm(ilPropertyFormGUI $parent_form = NULL, $first = true) {
		if ($parent_form) {
			$form = $parent_form;
		} else {
			$form = new ilPropertyFormGUI();
		}
		if ($first) {
			$h = new ilFormSectionHeaderGUIFixed();
			$form->addItem($h);
		}
		$h = new ilFormSectionHeaderGUIFixed();
		if ($this->parent->object->getShowBlockTitlesDuringEvaluation()) {
			$h->setTitle($this->object->getTitle());
		} else {
			$h->setTitle(''); // set an empty title to keep the optical separation of blocks
		}
		if ($this->parent->object->getShowBlockDescriptionsDuringEvaluation()) {
			$h->setInfo($this->object->getDescription());
		}
		$form->addItem($h);

		return $form;
	}


	protected function setObjectValuesByPost() {
		$this->object->setParentId($this->parent->object->getId());
		$this->object->setTitle($this->form->getInput('title'));
		$this->object->setDescription($this->form->getInput('description'));
	}
}

?>