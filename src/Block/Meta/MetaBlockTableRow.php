<?php
namespace ilub\plugin\SelfEvaluation\Block\Meta;

use ilub\plugin\SelfEvaluation\Block\BlockTableRow;
use ilub\plugin\SelfEvaluation\Block\BlockTableAction;
use ilCtrl;
use ilSelfEvaluationPlugin;
use ilUtil;

class MetaBlockTableRow extends BlockTableRow
{

    public function __construct(ilCtrl $ilCtrl,
        ilSelfEvaluationPlugin $plugin,
        MetaBlock $block)
    {
        parent::__construct($ilCtrl,$plugin,$block);

        $this->setQuestionCount(count($block->getMetaContainer()->ge()));
        $question_action = $this->getQuestionAction();
        $this->setQuestionsLink($question_action->getLink());
        $this->addAction($question_action);

        $this->setFeedbackCount('-');
        $img_path = ilUtil::getImagePath('icon_ok.svg');
        $this->setStatusImg($img_path);
    }

    protected function saveCtrlParameters()
    {
        $this->ctrl->setParameterByClass('ilSelfEvaluationMetaBlockGUI', 'block_id', $this->getBlockId());
        $this->ctrl->setParameterByClass('ilSelfEvaluationMetaQuestionGUI', 'block_id', $this->getBlockId());
    }

    protected function getQuestionAction() : BlockTableAction
    {
        $title = $this->plugin->txt('edit_questions');
        $link = $this->ctrl->getLinkTargetByClass('ilSelfEvaluationMetaQuestionGUI', 'listFields');
        $cmd = 'edit_questions';

        return new BlockTableAction($title, $cmd, $link);
    }
}