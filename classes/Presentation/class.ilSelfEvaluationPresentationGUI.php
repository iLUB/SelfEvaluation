<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once(dirname( dirname(__FILE__) ) . '/class.ilObjSelfEvaluationGUI.php');
require_once(dirname( dirname(__FILE__) )  . '/Block/class.ilSelfEvaluationQuestionBlockGUI.php');
require_once(dirname( dirname(__FILE__) )  . '/Block/class.ilSelfEvaluationMetaBlockGUI.php');
require_once(dirname( dirname(__FILE__) )  . '/Block/class.ilSelfEvaluationVirtualQuestionBlock.php');
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
        $this->tpl->addCss("Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/templates/css/bootstrap.css");
        $this->tpl->addCss("Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/templates/css/presentation.css");
		$content = $this->pl->getTemplate('default/Dataset/tpl.dataset_presentation.html');
		$content->setVariable('INTRO_HEADER', $this->pl->txt('intro_header'));
		$content->setVariable('INTRO_BODY', $this->parent->object->getIntro());
		if ($this->parent->object->isActive()) {
			$content->setCurrentBlock('button');
			if (ilSelfEvaluationDataset::_datasetExists($this->identity->getId())) {
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
            }
            else{
                $content->setVariable('START_BUTTON', $this->pl->txt('start_button'));
                $content->setVariable('START_HREF', $this->ctrl->getLinkTarget($this, 'startEvaluation'));

			}
			$content->parseCurrentBlock();
		} else {
			ilUtil::sendInfo($this->pl->txt('not_active'));
		}
		$this->tpl->setContent($content->get());
	}


	public function startNewEvaluation() {
        unset($_SESSION['shuffled_blocks']);
        $_SESSION['xsev_data']['creation_date_dataset'] = time();
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

        $factory = new ilSelfEvaluationBlockFactory($this->parent->object->getId());
        $blocks = $factory->getAllBlocks();

        if($this->parent->object->getSortType()==ilObjSelfEvaluation::SHUFFLE_ACROSS_BLOCKS){
            if(empty($_SESSION['shuffled_blocks'])){
                $_SESSION['shuffled_blocks'] = serialize($this->orderMixedBlocks($blocks));
            }
            $blocks = unserialize($_SESSION['shuffled_blocks']);
        }

        if($this->parent->object->getDisplayType() == ilObjSelfEvaluation::DISPLAY_TYPE_SINGLE_PAGE){
            $this->displayAllBlocks($blocks,$mode);
        }
        else{
            $this->displaySingleBlock($blocks,$mode);
        }

        $this->form->addCommandButton('cancel', $this->pl->txt('cancel'));
		$this->form->setFormAction($this->ctrl->getFormAction($this));
	}

    /**
     * @param $blocks
     * @return mixed
     */
    protected function orderMixedBlocks($blocks){
        $return_blocks = array();
        /**
         * ilSelfEvaluationVirtualQuestionBlock[]
         */
        $virtual_blocks = array();
        $questions = array();

        $meta_blocks_end_form = array();
        $meta_block_beginning = true;

        foreach ($blocks as $block) {
            if(get_class($block) == 'ilSelfEvaluationMetaBlock'){
                if($meta_block_beginning){
                    $return_blocks[] = $block;
                }
                else{
                    $meta_blocks_end_form[] = $block;
                }
            }
            else{
                $meta_block_beginning = false;
                foreach(ilSelfEvaluationQuestion::_getAllInstancesForParentId($block->getId()) as $question){
                    $questions[] = $question;
                }
            }
        }
        shuffle($questions);

        $questions_in_block = 0;
        $block_nr = 0;
        $virtual_blocks[0] = new ilSelfEvaluationVirtualQuestionBlock($this->parent->object->getId());
        $virtual_blocks[$block_nr]->setTitle($this->pl->txt("mixed_block_title").$block_nr);
        $virtual_blocks[$block_nr]->setDescription($this->pl->txt("mixed_block_title").$block_nr);

        foreach($questions as $question){
            if($questions_in_block>=$this->parent->object->getSortRandomNrItemBlock()){
                $questions_in_block = 0;
                $block_nr++;
                $virtual_blocks[$block_nr] = new ilSelfEvaluationVirtualQuestionBlock($this->parent->object->getId());
                $virtual_blocks[$block_nr]->setTitle($this->pl->txt("mixed_block_title").$block_nr);
                $virtual_blocks[$block_nr]->setDescription($this->pl->txt("mixed_block_title").$block_nr);
            }
            $virtual_blocks[$block_nr]->addQuestion($question);
            $questions_in_block++;
        }

        foreach($virtual_blocks as $virtual_block){
            $return_blocks[] = $virtual_block;
        }

        foreach($meta_blocks_end_form as $meta_block){
            $return_blocks[] = $meta_block;
        }

        return $return_blocks;
    }

    protected function displaySingleBlock($blocks, $mode = 'new'){
        $page = $_GET['page'] ? $_GET['page'] : 1;
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

    protected function displayAllBlocks($blocks, $mode = 'new'){
        foreach ($blocks as $block) {
            $this->addBlockHtmlToForm($block);
        }
        $this->form->addCommandButton($mode . 'Data', $this->pl->txt('send_' . $mode));
    }

    protected function addBlockHtmlToForm($block){
        $gui_class = "";
        switch(get_class($block)){
            case 'ilSelfEvaluationQuestionBlock':
            case 'ilSelfEvaluationVirtualQuestionBlock':
                $gui_class = 'ilSelfEvaluationQuestionBlockPresentationGUI';
                break;
            case 'ilSelfEvaluationMetaBlock':
                $gui_class = 'ilSelfEvaluationMetaBlockPresentationGUI';
                break;
        }
        /**
         * @var $block_gui ilSelfEvaluationBlockPresentationGUI
         */
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
            $dataset = ilSelfEvaluationDataset::_getNewInstanceForIdentifierId($this->identity->getId());
            $dataset->setCreationDate($_SESSION['xsev_data']['creation_date_dataset']);
			$dataset->saveValuesByPost(array_merge($_SESSION['xsev_data'], $_POST));
            $_SESSION['xsev_data'] = '';
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