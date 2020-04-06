<?php
require_once('class.ilSelfEvaluationBlockPresentationGUI.php');
require_once(dirname(__FILE__) . '/../Question/class.ilSelfEvaluationQuestionPresentationGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/InputGUIs/classes/class.ilMatrixHeaderGUI.php');

/**
 * GUI-Class SelfEvaluation
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id:
 */
class ilSelfEvaluationQuestionBlockPresentationGUI extends ilSelfEvaluationBlockPresentationGUI
{
    /**
     * @var ilSelfEvaluationQuestionBlock
     */
    protected $object;

    /**
     * @param ilObjSelfEvaluationGUI                 $parent
     * @param ilSelfEvaluationQuestionBlockInterface $block
     */
    function __construct(ilObjSelfEvaluationGUI $parent, ilSelfEvaluationQuestionBlockInterface $block)
    {
        $this->object = $block;
        $this->parent = $parent;
    }

    /**
     * @param ilSelfEvaluationPresentationFormGUI $parent_form
     * @param bool                                $first
     * @return ilSelfEvaluationPresentationFormGUI
     */
    public function getBlockForm(ilPropertyFormGUI $parent_form = null, $first = true)
    {
        $form = parent::getBlockForm($parent_form, $first);

        $scale = ilSelfEvaluationScale::_getInstanceByObjId($this->object->getParentId());
        $matrix_gui = new ilMatrixHeaderGUI();
        $matrix_gui->setScale($scale->getUnitsAsArray());
        $form->addItem($matrix_gui);

        $question_form_size = min(max(12 - ($scale->getAmount() + 1), 3), 6);
        $form->setQuestionFieldSize($question_form_size);

        $questions = $this->object->getQuestions();

        if ($this->parent->object->getSortType() == ilObjSelfEvaluation::SHUFFLE_IN_BLOCKS) {
            shuffle($questions);
        }
        foreach ($questions as $qst) {
            $qst_gui = new ilSelfEvaluationQuestionPresentationGUI($qst->getId());
            $qst_gui->getQuestionForm($form);
        }

        return $form;
    }
}

?>