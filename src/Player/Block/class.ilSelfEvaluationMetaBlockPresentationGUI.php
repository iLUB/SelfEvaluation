<?php
namespace ilub\plugin\SelfEvaluation\Player\Block;

use ilPropertyFormGUI;
use ilSelfEvaluationMetaBlock;
use ilObjSelfEvaluationGUI;
use ilSelfEvaluationMetaQuestionPresentationGUI;
use ilub\plugin\SelfEvaluation\Player\PlayerFormContainer;

class ilSelfEvaluationMetaBlockPresentationGUI extends BlockPlayerGUI
{
    /**
     * @var ilSelfEvaluationMetaBlock
     */
    protected $object;

    /**
     * @param ilObjSelfEvaluationGUI    $parent
     * @param ilSelfEvaluationMetaBlock $block
     */
    function __construct(ilObjSelfEvaluationGUI $parent, ilSelfEvaluationMetaBlock $block)
    {
        $this->object = $block;
        $this->parent = $parent;
    }

    public function getBlockForm(PlayerFormContainer $parent_form = null) : ilPropertyFormGUI
    {
        $form = parent::getBlockForm($parent_form);

        $question_gui = new ilSelfEvaluationMetaQuestionPresentationGUI($this->object->getMetaContainer());

        return $question_gui->getQuestionForm($form);
    }
}

