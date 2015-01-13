<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once(dirname( dirname(__FILE__) ) . '/class.ilObjSelfEvaluationGUI.php');
require_once(dirname( dirname(__FILE__) )  . '/Block/class.ilSelfEvaluationQuestionBlockGUI.php');
require_once(dirname( dirname(__FILE__) )  . '/Block/class.ilSelfEvaluationMetaBlockGUI.php');
require_once(dirname( dirname(__FILE__) )  . '/Identity/class.ilSelfEvaluationIdentity.php');
require_once(dirname( dirname(__FILE__) )  . '/Dataset/class.ilSelfEvaluationDataset.php');
require_once(dirname( dirname(__FILE__) )  . '/Dataset/class.ilSelfEvaluationData.php');
require_once(dirname( dirname(__FILE__) )  . '/Question/class.ilSelfEvaluationQuestionGUI.php');
require_once(dirname( dirname(__FILE__) )  . '/Presentation/class.ilKnobGUI.php');
require_once('class.ilSelfEvaluationPresentationFormGUI.php');
require_once('Block/class.ilSelfEvaluationQuestionBlockPresentationGUI.php');
require_once('Block/class.ilSelfEvaluationMetaBlockPresentationGUI.php');
require_once(dirname(dirname(__FILE__) ) . '/Block/class.ilSelfEvaluationBlockFactory.php');

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
		$this->pl = $parent->getPluginObject();
		if (! $_GET['uid']) {
			ilUtil::sendFailure($this->pl->txt('uid_not_given'), true);
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
			case 'newNextPage':
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
		$this->form = new ilSelfEvaluationPresentationFormGUI();
		$this->form->setId('evaluation_form');

		switch ($this->parent->object->getDisplayType()) {
			case ilObjSelfEvaluation::DISPLAY_TYPE_SINGLE_PAGE:
                $this->displayAllBlocks($mode);
				break;

			case ilObjSelfEvaluation::DISPLAY_TYPE_MULTIPLE_PAGES:
                $this->displaySingleBlock($mode);
                break;
			case ilObjSelfEvaluation::DISPLAY_TYPE_ALL_QUESTIONS_SHUFFLED:
				/*
				 * TODO this is the draft of the "all questions shuffled" mode from Fabian Schmid.
				 * Ideally, make a general blockRenderGUI and feed it the questions you want displayed,
				 */
				$h = new ilFormSectionHeaderGUI();
				$h->setTitle($this->parent->object->getTitle());
				$this->form->addItem($h);
				$this->form = ilSelfEvaluationQuestionGUI::getAllQuestionsForms($this->parent, $this->form);
				$this->form->addCommandButton($mode . 'Data', $this->pl->txt('send_' . $mode));
				$this->form->addCommandButton('cancel', $this->pl->txt('cancel'));
				break;
		}
        $this->form->addCommandButton('cancel', $this->pl->txt('cancel'));
		$this->form->setFormAction($this->ctrl->getFormAction($this));
	}

    protected function displaySingleBlockForm($mode = 'new'){
        $factory = new ilSelfEvaluationBlockFactory($this->parent->object->getId());
        $page = $_GET['page'] ? $_GET['page'] : 1;
        $blocks = $factory->getAllBlocks();
        $last_page = count($blocks);

        if ($last_page > 1) {
            $this->form->addKnob($page,$last_page);
        }
        $this->ctrl->setParameter($this, 'page', $page);
        if ($page < $last_page) {
            $this->form->addCommandButton($mode . 'NextPage', $this->pl->txt('next_' . $mode));
        } else {
            $this->form->addCommandButton($mode . 'Data', $this->pl->txt('send_' . $mode));
        }
        $this->addBlockHtmlToForm($blocks[$page - 1]);

    }

    protected function displayAllBlocks($mode = 'new'){

        $factory = new ilSelfEvaluationBlockFactory($this->parent->object->getId());
        foreach ($factory->getAllBlocks() as $block) {
            $this->addBlockHtmlToForm($block);
        }
        $this->form->addCommandButton($mode . 'Data', $this->pl->txt('send_' . $mode));
    }

    protected function addBlockHtmlToForm($block){
        /**
         * @var ilSelfEvaluationBlockGUI $block_gui
         */
        $gui_class = get_class($block) . 'PresentationGUI';
        $block_gui = new $gui_class($this->parent, $block);
        $this->form = $block_gui->getBlockForm($this->form);
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


	public function newNextPage() {
		$this->initPresentationForm();
		if ($this->form->checkinput()) {
			if (is_array($_SESSION['xsev_data'])) {
				$_SESSION['xsev_data'] = array_merge($_SESSION['xsev_data'], $_POST);
			} else {
				$_SESSION['xsev_data'] = $_POST;
			}
			$this->ctrl->setParameter($this, 'page', $_GET['page'] + 1);
			$this->ctrl->redirect($this, 'startEvaluation');
		}
		$this->form->setValuesByPost();
		$this->tpl->setContent($this->form->getHTML());
	}


	public function newData() {
		$this->initPresentationForm();
		if ($this->form->checkinput()) {
			if (is_array($_SESSION['xsev_data'])) {
				$_POST = array_merge($_SESSION['xsev_data'], $_POST);
				$_SESSION['xsev_data'] = '';
			}
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
	private function redirectToResults(ilSelfEvaluationDataset $dataset) {
		$this->ctrl->setParameterByClass('ilSelfEvaluationDatasetGUI', 'dataset_id', $dataset->getId());
		$this->ctrl->redirectByClass('ilSelfEvaluationDatasetGUI', 'show');
	}
}

?>