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
	$ilDB->createTable(ilObjSelfEvaluation::TABLE_NAME, $fields);
	$ilDB->addPrimaryKey(ilObjSelfEvaluation::TABLE_NAME, array( 'id' ));
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

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Block/class.ilSelfEvaluationBlock.php');
$block = new ilSelfEvaluationBlock();
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
