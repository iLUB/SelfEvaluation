<#1>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilSelfEvaluationPlugin.php');


$pl = new ilSelfEvaluationPlugin();
$conf = $pl->getConfigObject();
$conf->initDB();
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

$ilDB->createTable(ilObjSelfEvaluation::TABLE_NAME, $fields);
$ilDB->addPrimaryKey(ilObjSelfEvaluation::TABLE_NAME, array( 'id' ));
?>
<#3>
<?php


require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilSelfEvaluationScale.php');
$block = new ilSelfEvaluationScale();
$block->initDB();

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilSelfEvaluationBlock.php');
$block = new ilSelfEvaluationBlock();
$block->initDB();

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilSelfEvaluationScaleUnit.php');
$block = new ilSelfEvaluationScaleUnit();
$block->initDB();

?>
<#4>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilSelfEvaluationQuestion.php');
$block = new ilSelfEvaluationQuestion();
$block->initDB();