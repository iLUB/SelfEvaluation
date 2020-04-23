<?php
namespace ilub\plugin\SelfEvaluation\Player;

use ilPropertyFormGUI;
use ilObjSelfEvaluationGUI;
use ilGlobalTemplateInterface;
use ilCtrl;
use ilSelfEvaluationPlugin;
use ilub\plugin\SelfEvaluation\Dataset\Dataset;
use ilUtil;
use ilub\plugin\SelfEvaluation\Identity\Identity;
use ilSelfEvaluationBlockFactory;
use ilub\plugin\SelfEvaluation\Dataset\Data;
use ilub\plugin\SelfEvaluation\Question\QuestionGUI;
use ilDBInterface;
use ilSelfEvaluationBlockPresentationGUI;
use ilObjSelfEvaluation;
use ilSelfEvaluationVirtualQuestionBlock;
use ilub\plugin\SelfEvaluation\Question\Question;

class PlayerGUI
{
    /**
     * @var ilPropertyFormGUI
     */
    protected $form;

    /**
     * @var int
     */
    protected $ref_id = 0;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjSelfEvaluationGUI
     */
    protected $parent;

    /**
     * @var ilSelfEvaluationPlugin
     */
    protected $plugin;

    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @var Identity
     */
    protected $identity;

    const SESSION_KEY = "xsev_data";
    const SESSION_KEY_CREATION_DATE = "creation_date";
    const SESSION_KEY_SHUFFLE = "shuffled_blocks";

    function __construct(ilDBInterface $db,ilObjSelfEvaluationGUI $parent, ilGlobalTemplateInterface $tpl, ilCtrl $ilCtrl, ilSelfEvaluationPlugin $plugin)
    {
        $this->db = $db;
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->parent = $parent;
        $this->plugin =  $plugin;

        $this->ref_id = $this->parent->object->getRefId();
    }

    public function executeCommand()
    {
        if (!$_GET['uid']) {
            ilUtil::sendFailure($this->plugin->txt('uid_not_given'), true);
            $this->ctrl->redirect($this->parent);
        } else {
            $this->identity = new Identity($this->db, $_GET['uid']);
        }

        $this->ctrl->saveParameter($this, 'uid');

        $this->performCommand();
    }

    /**
     * @return string
     */
    public function getStandardCommand()
    {
        return 'showContent';
    }

    function performCommand()
    {
        $cmd = ($this->ctrl->getCmd()) ? $this->ctrl->getCmd() : $this->getStandardCommand();

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

    public function cancel()
    {
        $this->ctrl->redirect($this->parent);
    }

    public function startScreen()
    {
        $this->tpl->addCss($this->plugin->getStyleSheetLocation("css/presentation.css"));
        $content = $this->plugin->getTemplate('default/Dataset/tpl.dataset_presentation.html');
        $content->setVariable('INTRO_HEADER', $this->plugin->txt('intro_header'));
        $content->setVariable('INTRO_BODY', $this->parent->object->getIntro());
        if ($this->parent->object->isActive()) {
            $content->setCurrentBlock('button');
            if (Dataset::_datasetExists($this->db, $this->identity->getId())) {
                if ($this->parent->object->getAllowMultipleDatasets()) {
                    $content->setVariable('START_BUTTON', $this->plugin->txt('start_new_button'));
                    $content->setVariable('START_HREF', $this->ctrl->getLinkTarget($this, 'startNewEvaluation'));
                    $content->parseCurrentBlock();
                    $content->setCurrentBlock('button');
                }
                if ($this->parent->object->getAllowDatasetEditing()) {
                    $content->setVariable('START_BUTTON', $this->plugin->txt('resume_button'));
                    $content->setVariable('START_HREF', $this->ctrl->getLinkTarget($this, 'resumeEvaluation'));
                    $content->parseCurrentBlock();
                    $content->setCurrentBlock('button');
                }
                if (!$this->parent->object->getAllowMultipleDatasets()
                    AND !$this->parent->object->getAllowDatasetEditing()
                ) {
                    ilUtil::sendInfo($this->plugin->txt('msg_already_filled'));
                }
            } else {
                $content->setVariable('START_BUTTON', $this->plugin->txt('start_button'));
                $content->setVariable('START_HREF', $this->ctrl->getLinkTarget($this, 'startEvaluation'));

            }
            $content->parseCurrentBlock();
        } else {
            ilUtil::sendInfo($this->plugin->txt('not_active'));
        }
        $this->tpl->setContent($content->get());
    }

    public function startNewEvaluation()
    {
        $_SESSION[self::SESSION_KEY][$this->ref_id] = null;
        $_SESSION[self::SESSION_KEY][$this->ref_id][self::SESSION_KEY_CREATION_DATE] = time();
        $this->startEvaluation();
    }

    public function startEvaluation()
    {
        $this->initPresentationForm();
        if (!is_array($_SESSION[self::SESSION_KEY][$this->ref_id])
            || !array_key_exists(self::SESSION_KEY_CREATION_DATE, $_SESSION[self::SESSION_KEY][$this->ref_id])) {
            $_SESSION[self::SESSION_KEY][$this->ref_id][self::SESSION_KEY_CREATION_DATE] = time();
        }
        $this->tpl->setContent($this->form->getHTML());
    }

    public function resumeEvaluation()
    {
        $this->initPresentationForm('update');
        $this->fillForm();
        $this->tpl->setContent($this->form->getHTML());
    }

    public function initPresentationForm($mode = 'new')
    {
        $this->form = new PlayerFormContainer();
        $this->form->setId('evaluation_form');

        $factory = new ilSelfEvaluationBlockFactory($this->parent->object->getId());
        $blocks = $factory->getAllBlocks();

        if ($this->parent->object->getSortType() == ilObjSelfEvaluation::SHUFFLE_ACROSS_BLOCKS) {
            if (empty($_SESSION[self::SESSION_KEY][$this->ref_id][self::SESSION_KEY_SHUFFLE])) {
                $_SESSION[self::SESSION_KEY][$this->ref_id][self::SESSION_KEY_SHUFFLE] = serialize($this->orderMixedBlocks($blocks));
            }

            $blocks = unserialize($_SESSION[self::SESSION_KEY][$this->ref_id][self::SESSION_KEY_SHUFFLE]);
        }

        if ($this->parent->object->getDisplayType() == ilObjSelfEvaluation::DISPLAY_TYPE_SINGLE_PAGE) {
            $this->displayAllBlocks($blocks, $mode);
        } else {
            $this->displaySingleBlock($blocks, $mode);
        }

        $this->form->addCommandButton('cancel', $this->plugin->txt('cancel'));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
    }

    /**
     * @param $blocks
     * @return mixed
     */
    protected function orderMixedBlocks($blocks)
    {
        $return_blocks = [];
        /**
         * ilSelfEvaluationVirtualQuestionBlock[]
         */
        $virtual_blocks = [];
        $questions = [];

        $meta_blocks_end_form = [];
        $meta_block_beginning = true;

        foreach ($blocks as $block) {
            if (get_class($block) == 'ilSelfEvaluationMetaBlock') {
                if ($meta_block_beginning) {
                    $return_blocks[] = $block;
                } else {
                    $meta_blocks_end_form[] = $block;
                }
            } else {
                $meta_block_beginning = false;
                foreach (Question::_getAllInstancesForParentId($this->db, $block->getId()) as $question) {
                    $questions[] = $question;
                }
            }
        }
        shuffle($questions);

        $questions_in_block = 0;
        $block_nr = 0;
        $virtual_blocks[0] = new ilSelfEvaluationVirtualQuestionBlock($this->parent->object->getId());
        $virtual_blocks[$block_nr]->setTitle($this->plugin->txt("mixed_block_title") . " " . ($block_nr + 1));
        $virtual_blocks[$block_nr]->setDescription($this->parent->object->getBlockOptionRandomDesc());

        foreach ($questions as $question) {
            if ($questions_in_block >= $this->parent->object->getSortRandomNrItemBlock()) {
                $questions_in_block = 0;
                $block_nr++;
                $virtual_blocks[$block_nr] = new ilSelfEvaluationVirtualQuestionBlock($this->parent->object->getId());
                $virtual_blocks[$block_nr]->setTitle($this->plugin->txt("mixed_block_title") . " " . ($block_nr + 1));
                $virtual_blocks[$block_nr]->setDescription($this->parent->object->getBlockOptionRandomDesc());
            }
            $virtual_blocks[$block_nr]->addQuestion($question);
            $questions_in_block++;
        }

        foreach ($virtual_blocks as $virtual_block) {
            $return_blocks[] = $virtual_block;
        }

        foreach ($meta_blocks_end_form as $meta_block) {
            $return_blocks[] = $meta_block;
        }

        return $return_blocks;
    }

    protected function displaySingleBlock($blocks, $mode = 'new')
    {
        $page = $_GET['page'] ? $_GET['page'] : 1;
        $last_page = count($blocks);

        if ($last_page > 1) {
            $this->form->addKnob($page, $last_page);
        }
        $this->ctrl->setParameter($this, 'page', $page);
        if ($page < $last_page) {
            $this->form->addCommandButton($mode . 'NextPage', $this->plugin->txt('next_' . $mode));
        } else {
            $this->form->addCommandButton($mode . 'Data', $this->plugin->txt('send_' . $mode));
        }
        $this->addBlockHtmlToForm($blocks[$page - 1]);

    }

    protected function displayAllBlocks($blocks, $mode = 'new')
    {
        foreach ($blocks as $block) {
            $this->addBlockHtmlToForm($block);
        }
        $this->form->addCommandButton($mode . 'Data', $this->plugin->txt('send_' . $mode));
    }

    protected function addBlockHtmlToForm($block)
    {
        $gui_class = "";
        switch (get_class($block)) {
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

    public function fillForm()
    {
        $dataset = Dataset::_getInstanceByIdentifierId($this->db, $this->identity->getId());
        $data = Data::_getAllInstancesByDatasetId($this->db,$dataset->getId());
        $array = [];
        foreach ($data as $d) {
            $array[QuestionGUI::POSTVAR_PREFIX . $d->getQuestionId()] = $d->getValue();
        }
        $this->form->setValuesByArray($array);
    }

    public function newNextPage()
    {
        $this->initPresentationForm();

        if ($this->form->checkinput()) {

            if (is_array($_SESSION[self::SESSION_KEY][$this->ref_id])) {
                $_SESSION[self::SESSION_KEY][$this->ref_id] = array_merge($_SESSION[self::SESSION_KEY][$this->ref_id],
                    $_POST);
            } else {
                $_SESSION[self::SESSION_KEY][$this->ref_id] = $_POST;
            }

            $this->ctrl->setParameter($this, 'page', $_GET['page'] + 1);
            $this->ctrl->redirect($this, 'startEvaluation');
        }

        $this->form->setValuesByPost();
        $this->tpl->setContent($this->form->getHTML());
    }

    public function newData()
    {
        $this->initPresentationForm();

        if ($this->form->checkinput()) {
            $dataset = Dataset::_getNewInstanceForIdentifierId($this->db,$this->identity->getId());
            $dataset->setCreationDate($_SESSION[self::SESSION_KEY][$this->ref_id][self::SESSION_KEY_CREATION_DATE]);
            $dataset->saveValuesByPost(array_merge($_SESSION[self::SESSION_KEY][$this->ref_id], $_POST));
            $_SESSION[self::SESSION_KEY][$this->ref_id] = '';
            $this->redirectToResults($dataset);
        }
        $this->form->setValuesByPost();
        $this->tpl->setContent($this->form->getHTML());
    }

    public function updateData()
    {
        $this->initPresentationForm();

        if ($this->form->checkinput()) {
            $dataset = Dataset::_getInstanceByIdentifierId($this->db, $this->identity->getId());
            $dataset->updateValuesByPost($_POST);
            //See #1017
            //ilUtil::sendSuccess($this->plugin->txt('data_saved'), true);
            $this->redirectToResults($dataset);
        }
        $this->form->setValuesByPost();
        $this->tpl->setContent($this->form->getHTML());
    }

    private function redirectToResults(Dataset $dataset)
    {
        $this->ctrl->setParameterByClass('ilSelfEvaluationDatasetGUI', 'dataset_id', $dataset->getId());
        $this->ctrl->redirectByClass('ilSelfEvaluationDatasetGUI', 'show');
    }
}

