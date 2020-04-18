<?php
require_once('class.ilSelfEvaluationVirtualQuestionBlock.php');

/**
 * Class ilSelfEvaluationVirtualOverallBlock
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */
class ilSelfEvaluationVirtualOverallBlock extends ilSelfEvaluationVirtualQuestionBlock
{


    function __construct(int $parent_obj_id, ilSelfEvaluationPlugin $plugin)
    {
        $this->setId($parent_obj_id);
        $this->setParentId($parent_obj_id);
        $this->setTitle($plugin->txt("overall_feedback_block"));
        $this->setDescription($plugin->txt("overall_feedback_block_description"));

    }
}

?>