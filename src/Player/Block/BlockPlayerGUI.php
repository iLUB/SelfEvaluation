<?php

namespace ilub\plugin\SelfEvaluation\Player\Block;

use ilPropertyFormGUI;
use ilSelfEvaluationBlock;
use ilObjSelfEvaluationGUI;
use ilub\plugin\SelfEvaluation\UIHelper\FormSectionHeaderGUIFixed;
use ilub\plugin\SelfEvaluation\Player\PlayerFormContainer;

abstract class BlockPlayerGUI
{
    /**
     * @var ilSelfEvaluationBlock
     */
    protected $object;

    /**
     * @var ilObjSelfEvaluationGUI
     */
    protected $parent;

    /**
     * @param PlayerFormContainer $parent_form
     * @return ilPropertyFormGUI
     */
    public function getBlockForm(PlayerFormContainer $parent_form = null) : ilPropertyFormGUI
    {
        if ($parent_form) {
            $form = $parent_form;
        } else {
            $form = new ilPropertyFormGUI();
        }

        $h = new FormSectionHeaderGUIFixed();

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

