<?php
require_once __DIR__ . '/../vendor/autoload.php';

use ilub\plugin\SelfEvaluation\Block\Matrix\QuestionBlock;
use ilub\plugin\SelfEvaluation\Question\Matrix\Question;
use ilub\plugin\SelfEvaluation\Question\Matrix\QuestionTableGUI;
use ilub\plugin\SelfEvaluation\Question\BaseQuestionGUI;

class QuestionGUI extends BaseQuestionGUI
{
    /**
     * @var ilPropertyFormGUI
     */
    protected $form;

    /**
     * @var QuestionBlock
     */
    protected $block;
    /**
     * @var Question
     */
    protected $question;

    protected function createTableGUI() : ilTable2GUI
    {
        return new QuestionTableGUI($this, $this->plugin, $this->tpl,'showContent', $this->block, $this->hasSorting());
    }

    public function initQuestionForm(string $mode = 'create')
    {
        parent::initQuestionForm($mode);
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

    public function setQuestionFormValues()
    {
        $values['title'] = $this->question->getTitle();
        $values['question_body'] = $this->question->getQuestionBody();
        $values['is_inverse'] = $this->question->getIsInverse();
        $this->form->setValuesByArray($values);
    }

    public function createQuestionSetFields()
    {
        $this->question->setTitle($this->form->getInput('title'));
        $this->question->setQuestionBody($this->form->getInput('question_body'));
        $this->question->setIsInverse($this->form->getInput('is_inverse'));
        $this->question->setParentId($this->block->getId());
    }
}