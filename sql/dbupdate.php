<#1>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilSelfEvaluationPlugin.php');

$pl = new ilSelfEvaluationPlugin();
$conf = $pl->getConfigObject();
$conf->initDB();
$conf->setAsync(true);
?>
<#2>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilObjSelfEvaluation.php');
$fields = [
    'id' => [
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
    ],
    'is_online' => [
        'type' => 'integer',
        'length' => 1,
    ],
    'evaluation_type' => [
        'type' => 'integer',
        'length' => 1,
    ],
    'sort_type' => [
        'type' => 'integer',
        'length' => 1,
    ],
    'display_type' => [
        'type' => 'integer',
        'length' => 1,
    ],
    'intro' => [
        'type' => 'clob',
    ],
    'outro' => [
        'type' => 'clob',
    ],
];
if (!$this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $this->db->createTable(ilObjSelfEvaluation::TABLE_NAME, $fields);
    $this->db->addPrimaryKey(ilObjSelfEvaluation::TABLE_NAME, ['id']);
}

?>
<#3>
<?php

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Scale/class.ilSelfEvaluationScaleUnit.php');
$block = new ilSelfEvaluationScaleUnit();
$block->initDB();

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Scale/class.ilSelfEvaluationScale.php');
$block = new ilSelfEvaluationScale();
$block->initDB();

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Block/class.ilSelfEvaluationQuestionBlock.php');
$block = new ilSelfEvaluationQuestionBlock();
$block->initDB();

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Question/class.ilSelfEvaluationQuestion.php');
$block = new ilSelfEvaluationQuestion();
$block->initDB();

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Dataset/class.ilSelfEvaluationData.php');
$data = new ilSelfEvaluationData();
$data->initDB();

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Dataset/class.ilSelfEvaluationDataset.php');
$dataset = new ilSelfEvaluationDataset();
$dataset->initDB();

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Identity/class.ilSelfEvaluationIdentity.php');
$id = new ilSelfEvaluationIdentity();
$id->initDB();

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Feedback/class.ilSelfEvaluationFeedback.php');
$id = new ilSelfEvaluationFeedback();
$id->initDB();

?>
<#4>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilObjSelfEvaluation.php');
/**
 * @var $ilDB ilDB
 */
if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $field = [
        'type' => 'integer',
        'length' => 1,
    ];
    $ilDB->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_questions', $field);

}

?>
<#5>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilObjSelfEvaluation.php');
/**
 * @var $ilDB ilDB
 */
if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $field = [
        'type' => 'integer',
        'length' => 1,
    ];
    $ilDB->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs', $field);
    $ilDB->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_charts', $field);
    $ilDB->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_overview', $field);
    $ilDB->dropTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_questions');

}

?>
<#6>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilObjSelfEvaluation.php');
/**
 * @var $ilDB ilDB
 */
if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $field = [
        'type' => 'integer',
        'length' => 1,
    ];
    $ilDB->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_block_titles_sev', $field);
    $ilDB->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_block_titles_fb', $field);
    $ilDB->manipulate('UPDATE ' . ilObjSelfEvaluation::TABLE_NAME .
        ' SET `show_block_titles_sev` = 1, `show_block_titles_fb` = 1;');
}
?>
<#7>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilObjSelfEvaluation.php');
/**
 * @var $ilDB ilDB
 */
if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $field = [
        'type' => 'integer',
        'length' => 1,
    ];
    $ilDB->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_block_desc_sev', $field);
    $ilDB->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_block_desc_fb', $field);
    $ilDB->manipulate('UPDATE ' . ilObjSelfEvaluation::TABLE_NAME .
        ' SET `show_block_desc_sev` = 1, `show_block_desc_fb` = 1;');
}
?>
<#8>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Block/class.ilSelfEvaluationBlock.php');
/**
 * @var $ilDB ilDB
 */
$block = new ilSelfEvaluationQuestionBlock();
if (!$ilDB->tableColumnExists($block->getTableName(), 'abbreviation')) {
    $field = [
        'type' => 'text',
        'length' => 1024,
    ];
    $ilDB->addTableColumn($block->getTableName(), 'abbreviation', $field);
}
?>
<#9>
<?php
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilSelfEvaluationPlugin.php');
$pl = new ilSelfEvaluationPlugin();
if ($this->db->tableExists('robjselfevaluation_c') AND !$this->db->tableExists($pl->getConfigTableName())) {
    $this->db->renameTable('robjselfevaluation_c', $pl->getConfigTableName());
}
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilObjSelfEvaluation.php');
/**
 * @var $ilDB ilDB
 */
if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $field = [
        'type' => 'clob',
    ];
    $ilDB->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'identity_selection_info', $field);
}
?>
<#10>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Block/class.ilSelfEvaluationMetaBlock.php');
/**
 * @var $ilDB ilDB
 */
$block = new ilSelfEvaluationMetaBlock();
$block->initDB();
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Dataset/class.ilSelfEvaluationData.php');
/**
 * @var $ilDB ilDB
 */
if (!$ilDB->tableColumnExists(ilSelfEvaluationData::TABLE_NAME, 'question_type')) {
    $field = [
        'type' => 'text',
        'length' => 1024,
        'notnull' => true
    ];
    $ilDB->addTableColumn(ilSelfEvaluationData::TABLE_NAME, 'question_type', $field);
    $ilDB->manipulate('UPDATE ' . ilSelfEvaluationData::TABLE_NAME .
        ' SET `question_type` = ' . $ilDB->quote(ilSelfEvaluationData::QUESTION_TYPE, 'text') . ';');
    $ilDB->modifyTableColumn(ilSelfEvaluationData::TABLE_NAME, 'value', ['type' => 'clob']);
}
?>
<#11>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilObjSelfEvaluation.php');
/**
 * @var $ilDB ilDB
 */
if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    if (!$ilDB->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'sort_random_nr_items_block')) {
        $field = [
            'type' => 'integer',
            'length' => 4,
        ];
        $ilDB->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'sort_random_nr_items_block', $field);
    }
}
?>
<#12>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilObjSelfEvaluation.php');
/**
 * @var $ilDB ilDB
 */
if ($this->db->tableExists(ilSelfEvaluationData::TABLE_NAME)) {
    if (!$ilDB->tableColumnExists(ilSelfEvaluationData::TABLE_NAME, 'creation_date')) {
        $field = [
            'type' => 'integer',
            'length' => 4
        ];
        $ilDB->addTableColumn(ilSelfEvaluationData::TABLE_NAME, 'creation_date', $field);
    }
}
?>
<#13>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Question/class.ilSelfEvaluationMetaQuestion.php');
/**
 * @var $ilDB ilDB
 */
if ($this->db->tableExists(ilSelfEvaluationMetaQuestion::TABLE_NAME)) {

    if (!$ilDB->tableColumnExists(ilSelfEvaluationMetaQuestion::TABLE_NAME,
        'short_title')) {
        $field = [
            'type' => 'text',
            'length' => 1024,
            'notnull' => true
        ];
        $ilDB->addTableColumn(ilSelfEvaluationMetaQuestion::TABLE_NAME, 'short_title', $field);
    }
}
?>
<#14>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilObjSelfEvaluation.php');
/**
 * @var $ilDB ilDB
 */
if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $field = [
        'type' => 'integer',
        'length' => 4
    ];
    if (!$ilDB->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_overview_bar')) {
        $ilDB->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_overview_bar', $field);
    }
    if (!$ilDB->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_overview_spider')) {
        $ilDB->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_overview_spider', $field);
    }
    if (!$ilDB->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_overview_left_right')) {
        $ilDB->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_overview_left_right', $field);
    }

    if (!$ilDB->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_chart_bar')) {
        $ilDB->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_chart_bar', $field);
    }
    if (!$ilDB->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_chart_spider')) {
        $ilDB->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_chart_spider', $field);
    }
    if (!$ilDB->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_chart_left_right')) {
        $ilDB->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_chart_left_right', $field);
    }

    $ilDB->manipulate('UPDATE ' . ilObjSelfEvaluation::TABLE_NAME .
        ' SET
			`show_fbs_overview_bar` = 1,
			`show_fbs_overview_spider` = 1,
			`show_fbs_overview_left_right` = 1,
			`show_fbs_chart_bar` = 1,
			`show_fbs_chart_spider` = 1,
			`show_fbs_chart_left_right` = 1
			;');

}
?>
<#15>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Feedback/class.ilSelfEvaluationFeedback.php');
$ilDB->modifyTableColumn(ilSelfEvaluationFeedback::TABLE_NAME, 'feedback_text', [
    'type' => 'clob'
]);
?>
<#16>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilObjSelfEvaluation.php');
/**
 * @var $ilDB ilDB
 */
if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $field = [
        'type' => 'text',
        'length' => 1024,
        'notnull' => true
    ];
    if (!$ilDB->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'outro_title')) {
        $ilDB->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'outro_title', $field);
    }
}
?>
<#17>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilObjSelfEvaluation.php');
/**
 * @var $ilDB ilDB
 */
if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $field = [
        'type' => 'integer',
        'length' => 4
    ];
    if (!$ilDB->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'bar_show_label_as_percentage')) {
        $ilDB->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'bar_show_label_as_percentage', $field);
    }
}
?>
<#18>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilObjSelfEvaluation.php');
/**
 * @var $ilDB ilDB
 */
if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $field = [
        'type' => 'clob'
    ];
    if (!$ilDB->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'block_option_random_desc')) {
        $ilDB->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'block_option_random_desc', $field);
    }
}
?>
<#19>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilObjSelfEvaluation.php');
/**
 * @var $ilDB ilDB
 */
if ($this->db->tableExists(ilSelfEvaluationFeedback::TABLE_NAME)) {
    $field = [
        'type' => 'integer',
        'length' => 4
    ];
    if (!$ilDB->tableColumnExists(ilSelfEvaluationFeedback::TABLE_NAME, 'parent_type_overall')) {
        $ilDB->addTableColumn(ilSelfEvaluationFeedback::TABLE_NAME, 'parent_type_overall', $field);
    }
}
?>
<#20>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilObjSelfEvaluation.php');
/**
 * @var $ilDB ilDB
 */
if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $field = [
        'type' => 'integer',
        'length' => 4
    ];
    if (!$ilDB->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_overview_text')) {
        $ilDB->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_overview_text', $field);
    }
}
?>
<#21>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilObjSelfEvaluation.php');
/**
 * @var $ilDB ilDB
 */
if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $field = [
        'type' => 'integer',
        'length' => 4
    ];
    if (!$ilDB->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_overview_statistics')) {
        $ilDB->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_overview_statistics', $field);
    }
}
?>
<#22>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilObjSelfEvaluation.php');
/**
 * @var $ilDB ilDB
 */
if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $field = [
        'type' => 'integer',
        'length' => 4
    ];
    if (!$ilDB->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'identity_selection')) {
        $ilDB->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'identity_selection', $field);
    }
}
?>
<#23>
<?php
/**
 * Overall Feedback used ref-id to reference the parent, this is an issue for copying and exporting.
 * This is hereby fixed by setting the obj_id as parent.
 */
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilObjSelfEvaluation.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Feedback/class.ilSelfEvaluationFeedback.php');
$overall_feedbacks = ilSelfEvaluationFeedback::_getAllInstances(true);
foreach ($overall_feedbacks as $overall_feedback) {
    if (ilObject::_lookupType($overall_feedback->getParentId(), true) == "xsev") {
        $self_eval = new ilObjSelfEvaluation($overall_feedback->getParentId());
        $obj_id = $self_eval->getId();
        $overall_feedback->setParentId($obj_id);
        $overall_feedback->update();
    } else {
        $overall_feedback->delete();
        throw new Exception("Step 23: Given ID is not ref-id of type xsev: " . $overall_feedback->getParentId() . " the Feedback has been deleted due to invalid data");
    }
}
?>

<#24>
<?php
if (!$ilDB->indexExistsByFields('rep_robj_xsev_d', ['i2'])) {
    $ilDB->addIndex('rep_robj_xsev_d', ['dataset_id', 'question_id', 'question_type'], 'i2');
}
?>