<?php
namespace ilub\plugin\SelfEvaluation\Question;

use ilObjSelfEvaluationGUI;
use ilGlobalPageTemplate;
use ilCtrl;
use ilToolbarGUI;
use ilSelfEvaluationPlugin;
use ilSelfEvaluationQuestionBlock;
use ilPropertyFormGUI;
use ilTextAreaInputGUI;
use ilTextInputGUI;
use ilUtil;
use ilConfirmationGUI;
use ilCheckboxInputGUI;
use ilDBInterface;

class QuestionGUI
{

    const POSTVAR_PREFIX = 'qst_';

    /**
     * @var ilPropertyFormGUI
     */
    protected $form;

    /**
     * @var ilSelfEvaluationQuestionBlock
     */
    protected $block;

    /**
     * @var Question
     */
    protected $question;

    /**
     * @var ilGlobalPageTemplate
     */
    protected $tpl;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

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

    function __construct(ilObjSelfEvaluationGUI $parent,
        ilGlobalPageTemplate $tpl,
        ilCtrl $ilCtrl,
        ilToolbarGUI $ilToolbar,
        ilSelfEvaluationPlugin $plugin,
        ilDBInterface $db,
        int $question_id = 0, int $block_id = 0)
    {
        $this->tpl = $tpl;
        $this->toolbar = $ilToolbar;
        $this->ctrl = $ilCtrl;
        $this->parent = $parent;
        $this->plugin = $plugin;
        $this->db = $db;
        $this->block = new ilSelfEvaluationQuestionBlock($block_id ? $block_id : (int) $_GET['block_id']);
        $this->question = new Question($this->db,$question_id ? $question_id : (int) $_GET['question_id']);
    }

    public function executeCommand()
    {
        $this->performCommand();
    }

    function performCommand()
    {
        $this->ctrl->saveParameter($this, 'block_id');
        $this->ctrl->saveParameter($this, 'question_id');

        $cmd = ($this->ctrl->getCmd()) ? $this->ctrl->getCmd() : $this->getStandardCommand();

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

    public function getStandardCommand()
    {
        return 'showContent';
    }

    public function addQuestion()
    {
        $this->initQuestionForm();
        $this->tpl->setContent($this->form->getHTML());
    }

    public function cancel()
    {
        $this->ctrl->redirectByClass('ilSelfEvaluationQuestionGUI');
    }

    /**
     * @param string $mode
     */
    public function initQuestionForm($mode = 'create')
    {
        $this->form = new  ilPropertyFormGUI();
        $this->form->setTitle($this->plugin->txt($mode . '_question'));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        $this->form->addCommandButton($mode . 'Object', $this->plugin->txt($mode . '_question_button'));
        $this->form->addCommandButton('cancel', $this->plugin->txt('cancel'));
        $te = new ilTextAreaInputGUI($this->plugin->txt('question_body'), 'question_body');
        $te->setRequired(true);
        $this->form->addItem($te);
        $te = new ilTextInputGUI($this->plugin->txt('short_title'), 'title');
        $te->setInfo($this->plugin->txt('question_title_info'));
        $te->setMaxLength(8);
        $te->setRequired(false);
        $this->form->addItem($te);
        $cb = new ilCheckboxInputGUI($this->plugin->txt('is_inverse'), 'is_inverse');
        $cb->setValue(1);
        $this->form->addItem($cb);
    }

    public function createObject()
    {
        $this->initQuestionForm();
        if ($this->form->checkInput()) {
            $this->question = new Question($this->db);
            $this->question->setTitle($this->form->getInput('title'));
            $this->question->setQuestionBody($this->form->getInput('question_body'));
            $this->question->setIsInverse($this->form->getInput('is_inverse'));
            $this->question->setParentId($this->block->getId());
            $this->question->create();
            ilUtil::sendSuccess($this->plugin->txt('msg_question_created'));
            $this->cancel();
        }
        $this->tpl->setContent($this->form->getHTML());
    }

    public function editQuestion()
    {
        $this->initQuestionForm('update');
        $this->setObjectValues();
        $this->tpl->setContent($this->form->getHTML());
    }

    public function setObjectValues()
    {
        $values['title'] = $this->question->getTitle();
        $values['question_body'] = $this->question->getQuestionBody();
        $values['is_inverse'] = $this->question->getIsInverse();
        $this->form->setValuesByArray($values);
    }

    public function updateObject()
    {
        $this->initQuestionForm();
        $this->form->setValuesByPost();
        if ($this->form->checkInput()) {
            $this->question->setTitle($this->form->getInput('title'));
            $this->question->setQuestionBody($this->form->getInput('question_body'));
            $this->question->setIsInverse($this->form->getInput('is_inverse'));
            $this->question->update();
            ilUtil::sendSuccess($this->plugin->txt('msg_question_updated'));
            $this->cancel();
        }
        $this->tpl->setContent($this->form->getHTML());
    }

    public function deleteQuestion()
    {
        ilUtil::sendQuestion($this->plugin->txt('qst_delete_question'));
        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setCancel($this->plugin->txt('cancel'), 'cancel');
        $conf->setConfirm($this->plugin->txt('delete_question'), 'deleteObject');
        $conf->addItem('question_id', $this->question->getId(), $this->question->getTitle());
        $this->tpl->setContent($conf->getHTML());
    }

    public function deleteObject()
    {
        ilUtil::sendSuccess($this->plugin->txt('msg_question_deleted'), true);
        $this->question->delete();
        $this->cancel();
    }

    public function showContent()
    {
        if ($this->block->isBlockSortable()) {
            $this->tpl->addJavaScript($this->plugin->getDirectory() . '/templates/sortable.js');
        }

        $this->toolbar->addButton('&lt;&lt; ' . $this->plugin->txt('back_to_blocks'),
            $this->ctrl->getLinkTargetByClass('ilSelfEvaluationListBlocksGUI', 'showContent'));
        $this->toolbar->addButton($this->plugin->txt('add_new_question'), $this->ctrl->getLinkTarget($this, 'addQuestion'));

        $table = new QuestionTableGUI($this, $this->plugin, 'showContent', $this->block);
        $table->setData(Question::_getAllInstancesForParentId($this->db,$this->block->getId(), true));
        $this->tpl->setContent($table->getHTML());
    }

    public function saveSorting()
    {
        foreach ($_POST['position'] as $k => $v) {
            $obj = new Question($v);
            $obj->setPosition($k + 1);
            $obj->update();
        }
        ilUtil::sendSuccess($this->plugin->txt('sorting_saved'), true);
        $this->ctrl->redirect($this, 'showContent');
    }

}