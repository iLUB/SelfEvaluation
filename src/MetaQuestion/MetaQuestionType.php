<?php

abstract class MetaQuestionType
{

    /**
     * Make sure the type id is unique (at least within the container)
     * @return int
     */
    abstract public function getId();

    /**
     * Return a title in the users translation
     * @return string
     */
    abstract public function getTypeName();

    /**
     * @param iLubFieldDefinitionTypeOption $option
     * @return iLubFieldDefinitionTypeOption
     */
    abstract public function getValueDefinitionInputGUI(iLubFieldDefinitionTypeOption &$option);

    /**
     * @param iLubFieldDefinitionTypeOption $item
     * @param array                         $values
     */
    abstract public function setValues(iLubFieldDefinitionTypeOption $item, $values = array());

    /**
     * @param ilPropertyFormGUI $form
     * @return array
     */
    abstract public function getValues(ilPropertyFormGUI $form);

    /**
     * Define how this type is displayed in an ilFormPropertyGUI
     * @param string $title
     * @param string $postvar
     * @param array  $values
     * @return ilFormPropertyGUI
     */
    abstract public function getPresentationInputGUI($title, $postvar, $values);

    /**
     * @return string
     */
    public function __toString()
    {
        return 'type_id=' . $this->getId();
    }

    /**
     * @param int $type_id
     * @param \iLubFieldDefinitionType[]
     * @return bool|\iLubFieldDefinitionType
     */
    public static function getTypeByTypeId($type_id, $types)
    {
        foreach ($types as $type) {
            if ($type instanceof iLubFieldDefinitionType AND $type->getId() == $type_id) {
                return $type;
            }
        }

        return false;
    }
} 