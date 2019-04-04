<?php
require_once('class.ilSelfEvaluationVirtualQuestionBlock.php');

/**
 * Class ilSelfEvaluationVirtualOverallBlock
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */
class ilSelfEvaluationVirtualOverallBlock extends ilSelfEvaluationVirtualQuestionBlock {
    /**
     * @var ilObjSelfEvaluationGUI
     */
    protected $parent_gui = null;

    /**
     * @param $parent_id
     */
    function __construct(ilObjSelfEvaluationGUI $parent) {
        $this->parent_gui = $parent;
        $this->setId($this->parent_gui->object->getId());
        $this->setParentId($this->parent_gui->ref_id);
        $this->setTitle($this->parent_gui->getPluginObject()->txt("overall_feedback_block"));
        $this->setDescription($this->parent_gui->getPluginObject()->txt("overall_feedback_block_description"));

    }
}
?>