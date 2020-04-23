<?php
namespace ilub\plugin\SelfEvaluation\Question\Meta\Type;

use ilPropertyFormGUI;
use ilSelfEvaluationPlugin;

abstract class MetaQuestionType
{
    abstract public function getId() : int;

    abstract public function getTypeName() : string;

    abstract public function getValueDefinitionInputGUI(ilSelfEvaluationPlugin $plugin,MetaTypeOption $option);

    abstract public function setValues(MetaTypeOption $item, array $values = []);

    abstract public function getValues(ilPropertyFormGUI $form);

    abstract public function getPresentationInputGUI(ilSelfEvaluationPlugin $plugin,string $title, string $postvar, array $values);

    public function __toString() : string
    {
        return 'type_id=' . $this->getId();
    }

    /**
     * @param int $type_id
     * @param self[]
     * @return bool|self
     */
    public static function getTypeByTypeId(int $type_id, $types)
    {
        foreach ($types as $type) {
            if ($type instanceof MetaQuestionType AND $type->getId() == $type_id) {
                return $type;
            }
        }

        return false;
    }
} 