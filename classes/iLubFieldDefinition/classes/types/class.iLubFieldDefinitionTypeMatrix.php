<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2014 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/iLubFieldDefinition/classes/types/class.iLubFieldDefinitionType.php');

/**
 * Class iLubFieldDefinitionTypeSelect
 *
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class iLubFieldDefinitionTypeMatrix extends iLubFieldDefinitionType {

	const TYPE_ID = 4;

	/**
	 * @return int
	 */
	public function getId() {
		return self::TYPE_ID;
	}


	/**
	 * Return a title in the users translation
	 *
	 * @return string
	 */
	public function getTypeName() {
		global $lng;
		$lng->loadLanguageModule('survey');

		return $lng->txt('SurveyMatrixQuestion');
	}


	/**
	 * @param iLubFieldDefinitionTypeOption $option
	 *
	 * @return iLubFieldDefinitionTypeOption
	 */
	public function getValueDefinitionInputGUI(iLubFieldDefinitionTypeOption &$option) {
		// Select Type Values
		require_once('Services/Form/classes/class.ilTextWizardInputGUI.php');
		require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/iLubFieldDefinition/classes/class.iLubFieldDefinitionLng.php');
		$lng = new iLubFieldDefinitionLng();

		$ty_se_mu = new ilTextWizardInputGUI($lng->getMatrixScale(), 'scale_' .
				$this->getId());
		$ty_se_mu->setRequired(true);
		$ty_se_mu->setSize(32);
		$ty_se_mu->setMaxLength(128);
		$ty_se_mu->setValues(array(''));
		$ty_se_mu->setInfo($lng->getMatrixScaleDescription());
		$option->addSubItem($ty_se_mu);

		$ty_se_mu = new ilTextWizardInputGUI($lng->getMatrixQuestion(),
				'question_' .
				$this->getId());
		$ty_se_mu->setRequired(true);
		$ty_se_mu->setSize(64);
		$ty_se_mu->setMaxLength(4096);
		$ty_se_mu->setValues(array(''));
		$ty_se_mu->setInfo($lng->getMatrixQuestionDescription());

		$option->addSubItem($ty_se_mu);

		return $option;
	}


	/**
	 * @param iLubFieldDefinitionTypeOption $item
	 * @param array                         $values
	 */
	public function setValues(iLubFieldDefinitionTypeOption $item, $values = array()) {
		$scale_values = self::getScaleFromArray($values);
		$question_values = self::getQuestionsFromArray($values);

		foreach ($item->getSubItems() as $sub_item) {
			if ($sub_item instanceof ilTextWizardInputGUI AND
					$sub_item->getPostVar() == 'scale_' . $this->getId()) {
				$sub_item->setValue($scale_values);
			}else if($sub_item instanceof ilTextWizardInputGUI AND
					$sub_item->getPostVar() == 'question_' . $this->getId()){
				$sub_item->setValue($question_values);
			}
		}
	}


	public static function getQuestionsFromArray($data){
		$questions = [];

		foreach($data as $key => $value){
			if (strpos($key, 'question_') !== false) {
				$questions[$key] = $value;
			}
		}
		return $questions;
	}

	public static function getScaleFromArray($data){
		$scale = [];

		foreach($data as $key => $value){
			if (strpos($key, 'scale_') !== false) {
				$scale[$key] = $value;
			}
		}
		return $scale;
	}

	/**
	 * @param ilPropertyFormGUI $form
	 *
	 * @return array
	 */
	public function getValues(ilPropertyFormGUI $form) {

		$scale = [];
		$questions = [];

		foreach($form->getInput('scale_' . $this->getId()) as $key => $item){
			$scale["scale_".$key] = $item;
		}

		foreach($form->getInput('question_' . $this->getId()) as $key => $item){
			$questions["question_".$key] = $item;
		}
		return array_merge($scale,$questions);
	}




	/**
	 * @param string $title
	 * @param string $postvar
	 * @param array  $values
	 *
	 * @return ilFormPropertyGUI
	 */
	public function getPresentationInputGUI($title, $postvar, $values) {
		require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/InputGUIs/classes/class.ilMatrixFieldInputGUI.php');
		require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/InputGUIs/classes/class.ilMatrixHeaderGUI.php');

		$scale_values = self::getScaleFromArray($values);
		$question_values = self::getQuestionsFromArray($values);

		$matrix_items = [];

		$header = new ilMatrixHeaderGUI($title,$postvar);
		$header->setScale($scale_values);
		$matrix_items[] = $header;

		foreach($question_values as $key => $question_value){
			$input_item = new ilMatrixFieldInputGUI($question_value,
					"".$postvar."[".$key."]");
			$input_item->setScale($scale_values);
			$matrix_items[] = $input_item;
		}

		return $matrix_items;
	}
}