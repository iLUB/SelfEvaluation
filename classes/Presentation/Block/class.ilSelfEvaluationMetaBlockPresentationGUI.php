<?php
require_once('class.ilSelfEvaluationBlockPresentationGUI.php');
require_once(dirname(__FILE__) . '/../Question/class.ilSelfEvaluationMetaQuestionPresentationGUI.php');

/**
 * GUI-Class SelfEvaluation
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id:
 */
class ilSelfEvaluationMetaBlockPresentationGUI  extends ilSelfEvaluationBlockPresentationGUI{
    /**
     * @var ilSelfEvaluationMetaBlock
     */
    protected $object;

    /**
     * @param ilObjSelfEvaluationGUI $parent
     * @param ilSelfEvaluationMetaBlock  $block
     */
    function __construct(ilObjSelfEvaluationGUI $parent,ilSelfEvaluationMetaBlock $block) {
        $this->object = $block;
        $this->parent = $parent;
    }
    /**
     * @param ilPropertyFormGUI $parent_form
     * @param bool              $first
     *
     * @return ilPropertyFormGUI
     */
    public function getBlockForm(ilPropertyFormGUI $parent_form = NULL, $first = true) {
        $form = parent::getBlockForm($parent_form, $first);

        require_once(dirname(__FILE__) . '/../Question/class.ilSelfEvaluationMetaQuestionPresentationGUI.php');
        $question_gui = new ilSelfEvaluationMetaQuestionPresentationGUI($this->object->getMetaContainer());

        return $question_gui->getQuestionForm($form);
    }
}
?>