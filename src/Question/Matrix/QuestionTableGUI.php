<?php
namespace ilub\plugin\SelfEvaluation\Question\Matrix;

use ilTable2GUI;
use ilub\plugin\SelfEvaluation\Block\Block;
use ilSelfEvaluationPlugin;
use ilAdvancedSelectionListGUI;

class QuestionTableGUI extends ilTable2GUI
{
    /**
     * @var ilSelfEvaluationPlugin
     */
    protected $plugin;

    /**
     * @var Block
     */
    protected $block;

    /**
     * @var bool
     */
    protected $sortable;

    function __construct(QuestionGUI $a_parent_obj,ilSelfEvaluationPlugin $plugin, string $a_parent_cmd, Block $block, bool $sortable)
    {
        $this->setId('sev_feedbacks');
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->plugin = $plugin;
        $this->block = $block;
        $this->sortable = $sortable;

        $this->setTitle($block->getTitle() . ': ' . $this->plugin->txt('question_table_title'));
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->ctrl->setParameterByClass('ilSelfEvaluationQuestionGUI', 'question_id', null);
        $this->ctrl->setParameterByClass('ilSelfEvaluationQuestionGUI', 'block_id', $block->getId());
        $this->setRowTemplate($this->plugin->getDirectory() . '/templates/default/Question/tpl.template_question_row.html');
        $this->initColumns();
    }

    protected function initColumns(){
        if ($this->sortable) {
            $this->tpl->addJavaScript($this->plugin->getDirectory() . '/templates/js/sortable.js');
            $this->addColumn('', 'position', '20px');
            $this->addMultiCommand('saveSorting', $this->plugin->txt('save_sorting'));
        }

        $this->addColumn($this->plugin->txt('title'), $this->sortable ? 'title' : false, 'auto');
        $this->addColumn($this->plugin->txt('question_body'), $this->sortable ? 'question_body' : false, 'auto');
        $this->addColumn($this->plugin->txt('is_inverted'), $this->sortable ? 'is_inverted' : false, 'auto');
        $this->addColumn($this->plugin->txt('actions'), $this->sortable ? 'actions' : false, 'auto');
    }

    /**
     * @param array $a_set
     */
    public function fillRow($a_set)
    {
        $obj = new Question($a_set['id']);
        $this->ctrl->setParameterByClass('ilSelfEvaluationQuestionGUI', 'question_id', $obj->getId());
        if ($this->sortable) {
            $this->tpl->setVariable('ID', $obj->getId());
        }
        $this->tpl->setVariable('TITLE', $obj->getTitle() ? $obj->getTitle() :
            $this->plugin->txt('question') . ' ' . $this->block->getPosition() . '.' . $obj->getPosition());
        $this->tpl->setVariable('EDIT_LINK',
            $this->ctrl->getLinkTargetByClass('ilSelfEvaluationQuestionGUI', 'editQuestion'));
        $this->tpl->setVariable('BODY', strip_tags($obj->getQuestionBody()));
        $this->tpl->setVariable('IS_INVERTED',
            $obj->getIsInverse() ? './Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/templates/images/ok.png' : './Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/templates/images/blank.png');
        // Actions
        $ac = new ilAdvancedSelectionListGUI();
        $ac->setId('question_' . $obj->getId());
        $ac->addItem($this->plugin->txt('edit_question'), 'edit_question',
            $this->ctrl->getLinkTargetByClass('ilSelfEvaluationQuestionGUI', 'editQuestion'));
        $ac->addItem($this->plugin->txt('delete_question'), 'delete_question',
            $this->ctrl->getLinkTargetByClass('ilSelfEvaluationQuestionGUI', 'deleteQuestion'));
        $ac->setListTitle($this->plugin->txt('actions'));
        //
        $this->tpl->setVariable('ACTIONS', $ac->getHTML());
    }
}