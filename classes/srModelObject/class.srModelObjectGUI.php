<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
/**
 * GUI-Class srModelObjectGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @version           $Id:
 *
 */
abstract class srModelObjectGUI {

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs_gui;
	/**
	 * @var ilPropertyFormGUI
	 */
	protected $form;
	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var
	 */
	protected $object = NULL;


	/**
	 * @param $parent_gui
	 */
	public function __construct($parent_gui) {
		global $tpl, $ilCtrl, $ilToolbar;
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->parent = $parent_gui;
		$this->toolbar = $ilToolbar;
		$this->tabs_gui = $this->parent->tabs_gui;
		$this->initObject();
		if (! $this->initLanguage()) {
			global $lng;
			$this->lng = $lng;
		}
	}


	/**
	 * @return bool
	 */
	public function executeCommand() {
		$cmd = $this->ctrl->getCmd() ? $this->ctrl->getCmd() : $this->getStandardCommand();
		$this->performCommand($cmd);

		return true;
	}


	/**
	 * @return bool
	 * @description fill $this->object with your Object
	 */
	abstract protected function initObject();


	/**
	 * @return string
	 * @description return the Name (string) of your StandardCommand
	 */
	abstract protected function getStandardCommand();


	/**
	 * @param $cmd
	 *
	 * @return bool
	 */
	abstract protected function  performCommand($cmd);


	/**
	 * @return bool
	 * @description set $this->lng with your LanguageObject or return false to use global Language
	 */
	abstract protected function initLanguage();


	/**
	 * @param string $mode
	 */
	public function initForm($mode = 'create') {
		$this->form = new  ilPropertyFormGUI();
		$this->form->setTitle($this->lng->txt($mode . '_form'));
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		$this->form->addCommandButton($mode . 'Object', $this->lng->txt($mode . '_button'));
		$this->form->addCommandButton('cancel', $this->lng->txt('cancel'));
	}


	public function listObjects() {
		$this->tpl->setContent('');
	}


	public function addObject() {
		$this->initForm();
		$this->tpl->setContent($this->form->getHTML());
	}


	public function createObject() {
	}


	public function editObject() {
		$this->initForm('update');
		$this->tpl->setContent($this->form->getHTML());
	}


	public function updateObject() {
	}


	public function confirmDeleteObject() {
		$confirmationGui = new ilConfirmationGUI();
		$confirmationGui->addItem('item1', 1, 'Item One');
		$confirmationGui->setConfirm('Delete', 'deleteObject');
		$confirmationGui->setCancel('Cancel', 'cancel');
		$this->tpl->setContent($confirmationGui->getHTML());
	}


	public function deleteObject() {
	}


	public function cancel() {
		$this->ctrl->redirect($this);
	}


	public function filter() {
	}


	public function resetFilter() {
	}
}

?>