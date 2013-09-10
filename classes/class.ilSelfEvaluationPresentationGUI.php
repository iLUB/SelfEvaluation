<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('class.ilObjSelfEvaluationGUI.php');
require_once(dirname(__FILE__) . '/Block/class.ilSelfEvaluationBlock.php');
require_once(dirname(__FILE__) . '/Block/class.ilSelfEvaluationBlockGUI.php');
require_once(dirname(__FILE__) . '/Identity/class.ilSelfEvaluationIdentity.php');
require_once(dirname(__FILE__) . '/Dataset/class.ilSelfEvaluationDataset.php');
require_once(dirname(__FILE__) . '/Dataset/class.ilSelfEvaluationData.php');
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
		if (! $_GET['uid']) {
			ilUtil::sendFailure($this->pl->txt('uid_not_given'));
			$this->ctrl->redirect($this->parent);
		} else {
			$this->identity = new ilSelfEvaluationIdentity($_GET['uid']);
		}
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
			case 'startScreen':
			case 'startEvaluation':
			case 'resumeEvaluation':
			case 'newData':
			case 'updateData':
			case 'cancel':
			case 'endScreen':
			case 'startNewEvaluation':
				//				$this->checkPermission('read'); FSX
				$this->$cmd();
				break;
		}
	}


	public function cancel() {
		$this->ctrl->redirect($this->parent);
	}


	public function startScreen() {
		/**
		 * @var $content ilTemplate
		 */
		$content = $this->pl->getTemplate('default/tpl.content.html');
		$content->setVariable('INTRO_HEADER', $this->pl->txt('intro_header'));
		$content->setVariable('INTRO_BODY', $this->parent->object->getIntro());
		if ($this->parent->object->isActive()) {
			$content->setCurrentBlock('button');
			switch (ilSelfEvaluationDataset::_datasetExists($this->identity->getId())) {
				case true:
					if ($this->parent->object->getAllowMultipleDatasets()) {
						$content->setVariable('START_BUTTON', $this->pl->txt('start_new_button'));
						$content->setVariable('START_HREF', $this->ctrl->getLinkTarget($this, 'startNewEvaluation'));
						$content->parseCurrentBlock();
						$content->setCurrentBlock('button');
					}
					if ($this->parent->object->getAllowDatasetEditing()) {
						$content->setVariable('START_BUTTON', $this->pl->txt('resume_button'));
						$content->setVariable('START_HREF', $this->ctrl->getLinkTarget($this, 'resumeEvaluation'));
						$content->parseCurrentBlock();
						$content->setCurrentBlock('button');
					}
					if (! $this->parent->object->getAllowMultipleDatasets()
						AND ! $this->parent->object->getAllowDatasetEditing()
					) {
						ilUtil::sendInfo($this->pl->txt('msg_already_filled'));
					}
					break;
				case false;
					$content->setVariable('START_BUTTON', $this->pl->txt('start_button'));
					$content->setVariable('START_HREF', $this->ctrl->getLinkTarget($this, 'startEvaluation'));
					break;
			}
			$content->parseCurrentBlock();
		} else {
			ilUtil::sendInfo($this->pl->txt('not_active'));
		}
		$this->tpl->setContent($content->get());
	}


	public function startNewEvaluation() {
		$this->startEvaluation();
	}


	public function startEvaluation() {
		$this->initPresentationForm();
		$this->tpl->setContent($this->form->getHTML());
	}


	public function resumeEvaluation() {
		$this->initPresentationForm('update');
		$this->fillForm();
		$this->tpl->setContent($this->form->getHTML());
	}


	public function initPresentationForm($mode = 'new') {
		$this->form = new ilPropertyFormGUI();
		$this->form->setId('evaluation_form');
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
			$this->redirectToResults($dataset);
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
			$this->redirectToResults($dataset);
		}
		$this->form->setValuesByPost();
		$this->tpl->setContent($this->form->getHTML());
	}


	/**
	 * @param ilSelfEvaluationDataset $dataset
	 */
	public function redirectToResults(ilSelfEvaluationDataset $dataset) {
		$this->ctrl->setParameterByClass('ilSelfEvaluationDatasetGUI', 'dataset_id', $dataset->getId());
		$this->ctrl->redirectByClass('ilSelfEvaluationDatasetGUI', 'show');
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