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
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'is_online' => array(
		'type' => 'integer',
		'length' => 1,
	),
	'evaluation_type' => array(
		'type' => 'integer',
		'length' => 1,
	),
	'sort_type' => array(
		'type' => 'integer',
		'length' => 1,
	),
	'display_type' => array(
		'type' => 'integer',
		'length' => 1,
	),
	'intro' => array(
		'type' => 'clob',
	),
	'outro' => array(
		'type' => 'clob',
	),
);
if (! $this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
	$this->db->createTable(ilObjSelfEvaluation::TABLE_NAME, $fields);
	$this->db->addPrimaryKey(ilObjSelfEvaluation::TABLE_NAME, array( 'id' ));
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
	$field = array(
		'type' => 'integer',
		'length' => 1,
	);
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
	$field = array(
		'type' => 'integer',
		'length' => 1,
	);
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
	$field = array(
		'type' => 'integer',
		'length' => 1,
	);
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
	$field = array(
		'type' => 'integer',
		'length' => 1,
	);
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
	$field = array(
		'type' => 'text',
		'length' => 1024,
	);
	$ilDB->addTableColumn($block->getTableName(), 'abbreviation', $field);
}
?>
<#9>
<?php
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilSelfEvaluationPlugin.php');
$pl = new ilSelfEvaluationPlugin();
if ($this->db->tableExists('robjselfevaluation_c') AND ! $this->db->tableExists($pl->getConfigTableName())) {
	$this->db->renameTable('robjselfevaluation_c', $pl->getConfigTableName());
}
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilObjSelfEvaluation.php');
/**
 * @var $ilDB ilDB
 */
if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
	$field = array(
		'type' => 'clob',
	);
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
	$field = array(
		'type' => 'text',
		'length' => 1024,
		'notnull' => true
	);
	$ilDB->addTableColumn(ilSelfEvaluationData::TABLE_NAME, 'question_type', $field);
	$ilDB->manipulate('UPDATE ' . ilSelfEvaluationData::TABLE_NAME .
		' SET `question_type` = ' . $ilDB->quote(ilSelfEvaluationData::QUESTION_TYPE, 'text') . ';');
	$ilDB->modifyTableColumn(ilSelfEvaluationData::TABLE_NAME, 'value', array('type' => 'clob'));
}
?>