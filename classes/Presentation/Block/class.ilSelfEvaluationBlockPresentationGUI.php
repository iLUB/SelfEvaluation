<?php
/**
 * GUI-Class SelfEvaluation
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id:
 */
abstract class ilSelfEvaluationBlockPresentationGUI {
    /**
     * @var ilSelfEvaluationBlock
     */
    protected $object;

    /**
     * @var ilObjSelfEvaluationGUI
     */
    protected $parent;

    /**
     * @param ilSelfEvaluationPresentationFormGUI $parent_form
     * @return ilPropertyFormGUI|ilSelfEvaluationPresentationFormGUI
     */
    public function getBlockForm(ilPropertyFormGUI $parent_form = NULL, $first = true) {
        if ($parent_form) {
            $form = $parent_form;
        } else {
            $form = new ilPropertyFormGUI();
        }
        $h = new ilFormSectionHeaderGUIFixed();
        if ($this->parent->object->getShowBlockTitlesDuringEvaluation()) {
            $h->setTitle($this->object->getTitle());
        } else {
            $h->setTitle(''); // set an empty title to keep the optical separation of blocks
        }
        if ($this->parent->object->getShowBlockDescriptionsDuringEvaluation()) {
            $h->setInfo($this->object->getDescription());
        }
        $form->addItem($h);

        return $form;
    }
}
?>