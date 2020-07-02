<?php
use ilub\plugin\SelfEvaluation\Block\BlockGUI;
use ilub\plugin\SelfEvaluation\Block\Meta\MetaBlock;

class MetaBlockGUI extends BlockGUI
{
    /**
     * @var MetaBlock
     */
    protected $object;

    function __construct(
        ilDBInterface $db,
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ilCtrl,
        ilAccessHandler $access,
        ilSelfEvaluationPlugin $plugin,
        ilObjSelfEvaluationGUI $parent
    ) {
        parent::__construct( $db, $tpl, $ilCtrl, $access, $plugin, $parent);

        $this->object = new MetaBlock($this->db, (int) $_GET['block_id']);
        $this->object->setParentId($this->parent->obj_id);
    }
}