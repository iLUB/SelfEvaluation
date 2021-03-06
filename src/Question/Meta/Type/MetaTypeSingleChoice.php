<?php
namespace ilub\plugin\SelfEvaluation\Question\Meta\Type;

use ilRadioGroupInputGUI;
use ilRadioOption;
use ilSelfEvaluationPlugin;

class MetaTypeSingleChoice extends MetaTypeSelect
{

    const TYPE_ID = 3;

    public function getId() : int
    {
        return self::TYPE_ID;
    }

    public function getTypeName() : string
    {
        return 'MetaTypeSingleChoice';
    }

    public function getPresentationInputGUI(ilSelfEvaluationPlugin $plugin,string $title, string $postvar, array $values)
    {
        $select = new ilRadioGroupInputGUI($title, $postvar);

        foreach ($values as $key => $value) {

            $select->addOption(new ilRadioOption($value, $key));
        }

        return $select;
    }
}