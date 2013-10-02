<?php
require_once(dirname(__FILE__) . '/../class.ilObjSelfEvaluationGUI.php');
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Services/Accordion/classes/class.ilAccordionGUI.php');
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
/**
 * GUI-Class ilSelfEvaluationIdentityGUI
 *
 * @author            Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @version           $Id:
 *
 * @ilCtrl_Calls      ilSelfEvaluationIdentityGUI: ilPersonalDesktopGUI, ilObjSelfEvaluationGUI
 * @ilCtrl_IsCalledBy ilSelfEvaluationIdentityGUI: ilPersonalDesktopGUI, ilObjSelfEvaluationGUI
 */
class ilSelfEvaluationIdentityGUI {

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs_gui;
	/**
	 * @var ilPropertyFormGUI
	 */
	protected $ex;
	/**
	 * @var ilPropertyFormGUI
	 */
	protected $new;


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
	}


	public function executeCommand() {
		$this->tabs_gui->setTabActive('content');
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
		return 'show';
	}


	/**
	 * @param $cmd
	 */
	function performCommand($cmd) {
		switch ($cmd) {
			case 'show':
			case 'addNew':
			case 'cancel':
			case 'startWithExistingUid':
			case 'startWithNewUid':
				//				$this->checkPermission('read'); FSX
				$this->$cmd();
				break;
		}
	}


	public function show() {
		$this->initExistingForm();
		$this->initNewForm();
		$this->pl->updateLanguages();
		$acc = new ilAccordionGUI();
		$acc->setOrientation(ilAccordionGUI::VERTICAL);
		$acc->addItem($this->pl->txt('start_with_new_identity'), $this->new->getHTML());
		$acc->addItem($this->pl->txt('start_with_existing_identity'), $this->ex->getHTML());
		$this->tpl->setContent($acc->getHTML());
	}


	public function initExistingForm() {
		$this->ex = new ilPropertyFormGUI();
		$this->ex->setFormAction($this->ctrl->getFormAction($this));
		$te = new ilTextInputGUI($this->pl->txt('uid'), 'uid');
		$te->setRequired(true);
		$this->ex->addItem($te);
		$this->ex->addCommandButton('startWithExistingUid', $this->pl->txt('start'));
	}


	public function initNewForm() {
		$this->new = new ilPropertyFormGUI();
		$this->new->setFormAction($this->ctrl->getFormAction($this));
		$te = new ilNonEditableValueGUI($this->pl->txt('new_uid'), 'uid');
		$te->setRequired(true);
		$this->new->addItem($te);
		$this->new->addCommandButton('startWithNewUid', $this->pl->txt('start'));
	}


	public function startWithExistingUid() {
		$this->initExistingForm();
		if ($this->ex->checkInput()) {
			$identifier = $this->ex->getInput('uid');
			if (ilSelfEvaluationIdentity::_identityExists($this->parent->object->getId(), $identifier)) {
				$id = ilSelfEvaluationIdentity::_getInstanceForObjId($this->parent->object->getId(), $identifier);
				$this->ctrl->setParameterByClass('ilSelfEvaluationPresentationGUI', 'uid', $id->getId());
				$this->ctrl->redirectByClass('ilSelfEvaluationPresentationGUI', 'startScreen');
			} else {
				ilUtil::sendFailure($this->pl->txt('uid_not_exists'), true);
				$this->ctrl->redirect($this, 'show');
			}
		}
		$this->ex->setValuesByPost();
		$this->tpl->setContent($this->ex->getHTML());
	}


	public function startWithNewUid() {
		$id = ilSelfEvaluationIdentity::_getInstanceForObjId($this->parent->object->getId(), NULL);
		$this->ctrl->setParameterByClass('ilSelfEvaluationPresentationGUI', 'uid', $id->getId());
		$this->ctrl->redirectByClass('ilSelfEvaluationPresentationGUI', 'startScreen');
	}


	public function cancel() {
		$this->ctrl->redirect($this);
	}
}

?>