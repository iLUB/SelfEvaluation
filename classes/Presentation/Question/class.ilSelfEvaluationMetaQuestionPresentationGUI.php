<?php
require_once('Customizing/global/plugins/Libraries/iLubFieldDefinition/classes/types/class.iLubFieldDefinitionType.php');
/**
 * Class ilSelfEvaluationMetaQuestionGUI
 *
 * @author       Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */
class ilSelfEvaluationMetaQuestionPresentationGUI{
    /**
     * @var iLubFieldDefinitionContainer
     */
    protected $container;


    /**
     * @param iLubFieldDefinitionContainer $container
     */
    public function __construct(iLubFieldDefinitionContainer $container) {
		$this->container = $container;
	}

	/**
	 * @param ilPropertyFormGUI $form
	 *
	 * @return ilPropertyFormGUI
	 */
	public function getQuestionForm(ilPropertyFormGUI $form) {
		$fields = $this->container->getFieldDefinitions();

		foreach ($fields as $field) {
			$type = iLubFieldDefinitionType::getTypeByTypeId($field->getTypeId(),ilSelfEvaluationMetaQuestionGUI::getTypesArray());
			$item = $type->getPresentationInputGUI($field->getName(), ilSelfEvaluationMetaQuestionGUI::POSTVAR_PREFIX . $field->getId(),
				$field->getValues());

			if ($field->isRequired()) {
				$item->setRequired(true);
			}

			$form->addItem($item);
		}

		return $form;
	}
} 