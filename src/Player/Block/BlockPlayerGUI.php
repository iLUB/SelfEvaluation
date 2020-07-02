<?php
namespace ilub\plugin\SelfEvaluation\Player\Block;

use ilPropertyFormGUI;
use ilub\plugin\SelfEvaluation\Block\Block;
use ilObjSelfEvaluationGUI;
use ilub\plugin\SelfEvaluation\UIHelper\FormSectionHeaderGUIFixed;
use ilub\plugin\SelfEvaluation\Player\PlayerFormContainer;
use ilDBInterface;
use ilSelfEvaluationPlugin;

abstract class BlockPlayerGUI
{
    /**
     * @var Block
     */
    protected $block;

    /**
     * @var ilObjSelfEvaluationGUI
     */
    protected $parent;

    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @var ilSelfEvaluationPlugin
     */
    protected $plugin;

    function __construct(ilDBInterface $db,ilSelfEvaluationPlugin $plugin, ilObjSelfEvaluationGUI $parent, Block $block)
    {
        $this->db = $db;
        $this->block = $block;
        $this->parent = $parent;
        $this->plugin = $plugin;
    }

    public function getBlockForm(PlayerFormContainer $parent_form) : PlayerFormContainer
    {
        if ($parent_form) {
            $form = $parent_form;
        } else {
            $form = new ilPropertyFormGUI();
        }

        $h = new FormSectionHeaderGUIFixed();

        if ($this->parent->object->isShowBlockTitlesDuringEvaluation()) {
            $h->setTitle($this->block->getTitle());
        } else {
            $h->setTitle(''); // set an empty title to keep the optical separation of blocks
        }
        if ($this->parent->object->isShowBlockDescriptionsDuringEvaluation()) {
            $h->setInfo($this->block->getDescription());
        }
        $form->addItem($h);

        return $form;
    }
}

