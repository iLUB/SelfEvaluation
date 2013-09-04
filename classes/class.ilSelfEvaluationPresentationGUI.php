<?php
require_once('class.ilObjSelfEvaluationGUI.php');
require_once('class.ilSelfEvaluationBlock.php');
require_once('class.ilSelfEvaluationBlockGUI.php');
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('class.ilSelfEvaluationIdentity.php');
require_once('class.ilSelfEvaluationDataset.php');
require_once('class.ilSelfEvaluationData.php');
/**
 * GUI-Class ilSelfEvaluationPresentationGUI
 *
 * @author            Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @version           $Id:
 *
 * @ilCtrl_Calls      ilSelfEvaluationPresentationGUI:
 * @ilCtrl_IsCalledBy ilSelfEvaluationPresentationGUI:
 */
class ilSelfEvaluationPresentationGUI {

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs_gui;
	/**
	 * @var ilPropertyFormGUI
	 */
	protected $form;


	function __construct(ilObjSelfEvaluationGUI $parent) {
		global $tpl, $ilCtrl, $ilUser;
		/**
		 * @var $tpl    ilTemplate
		 * @var $ilCtrl ilCtrl
		 * @var $ilUser ilObjUser
		 */
		$this->tpl = $tpl;
		$this->user = $ilUser;
		$this->ctrl = $ilCtrl;
		$this->parent = $parent;
		$this->tabs_gui = $this->parent->tabs_gui;
		$this->pl = new ilSelfEvaluationPlugin();
		$this->identity = new ilSelfEvaluationIdentity($_GET['uid'] ? $_GET['uid'] : 0);
	}


	public function executeCommand() {
		$this->tabs_gui->setTabActive('content');
		$this->ctrl->saveParameter($this, 'uid');
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
			case 'newData':
			case 'start':
			case 'resume':
			case 'resumeContent':
			case 'updateData':
			case 'cancel':
				//				$this->checkPermission('read'); FSX
				$this->$cmd();
				break;
		}
	}


	public function cancel() {
		$this->ctrl->redirect($this->parent);
	}


	public function start() {
		$identity = ilSelfEvaluationIdentity::_getNewInstanceForObjId($this->parent->object->getId());
		if (self::_isAnonymous($this->user->getId())) {
			$identity->setTextKey('LX' . rand(100, 999));
			ilUtil::sendFailure($this->pl->txt('anonymous_access_failed'), true);
			$this->ctrl->redirect($this->parent, 'showContent');
		} else {
			$identity->setUserId($this->user->getId());
			$identity->create();
		}
		$this->ctrl->setParameter($this, 'uid', $identity->getId());
		$this->ctrl->redirect($this, 'showContent');
	}


	public function resume() {
		$identity = ilSelfEvaluationIdentity::_getInstanceByForForObjId($this->parent->object->getId(), $this->user->getId());
		$this->ctrl->setParameter($this, 'uid', $identity->getId());
		$this->ctrl->redirect($this, 'resumeContent');
	}


	public function showContent() {
		$this->initPresentationForm();
		$this->tpl->setContent($this->form->getHTML());
	}


	public function resumeContent() {
		$this->initPresentationForm('update');
		$this->fillForm();
		$this->tpl->setContent($this->form->getHTML());
	}


	public function initPresentationForm($mode = 'new') {
		$this->form = new ilPropertyFormGUI();
		foreach (ilSelfEvaluationBlock::_getAllInstancesByParentId($this->parent->object->getId()) as $block) {
			$block_gui = new ilSelfEvaluationBlockGUI($this->parent, $block->getId());
			$this->form = $block_gui->getBlockForm($this->form);
		}
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		$this->form->addCommandButton($mode . 'Data', $this->pl->txt('send_' . $mode));
		$this->form->addCommandButton('cancel', $this->pl->txt('cancel'));
	}


	public function fillForm() {
		$dataset = ilSelfEvaluationDataset::_getInstanceByIdentifierId($this->identity->getId());
		$data = ilSelfEvaluationData::_getAllInstancesByDatasetId($dataset->getId());
		$array = array();
		foreach ($data as $d) {
			$array[ilSelfEvaluationQuestionGUI::POSTVAR_PREFIX . $d->getQuestionId()] = $d->getValue();
		}
		$this->form->setValuesByArray($array);
	}


	public function newData() {
		$this->initPresentationForm();
		if ($this->form->checkinput()) {
			$dataset = ilSelfEvaluationDataset::_getNewInstanceForIdentifierId($this->identity->getId());
			$dataset->saveValuesByPost($_POST);
			ilUtil::sendSuccess($this->pl->txt('data_saved'), true);
			$this->cancel();
		}
		$this->form->setValuesByPost();
		$this->tpl->setContent($this->form->getHTML());
	}


	public function updateData() {
		$this->initPresentationForm();
		if ($this->form->checkinput()) {
			$dataset = ilSelfEvaluationDataset::_getInstanceByIdentifierId($this->identity->getId());
			$dataset->updateValuesByPost($_POST);
			ilUtil::sendSuccess($this->pl->txt('data_saved'), true);
			$this->cancel();
		}
		$this->form->setValuesByPost();
		$this->tpl->setContent($this->form->getHTML());
	}


	//
	// HELPER
	//
	public static function _isAnonymous($user_id) {
		foreach (ilObjUser::_getUsersForRole(ANONYMOUS_ROLE_ID) as $u) {
			if ($u['usr_id'] == $user_id) {
				return true;
			}
		}

		return false;
	}
}

?>