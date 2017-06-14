<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs
/LICENSE */
include_once('./Services/Form/classes/class.ilCustomInputGUI.php');
require_once('./Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php');
/**
 * Class ilMultipleTextInputGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 */
class ilMatrixFieldInputGUI extends ilCustomInputGUI {

	/**
	 * @var string
	 */
	protected $value;
	/**
	 * @var array
	 */
	protected $values;
	/**
	 * @var array
	 */
	protected $scale = array();


	public function getHtml() {
		return $this->buildHTML();
	}


	/**
	 * @return string
	 */
	private function buildHTML() {
		$tpl = new ilTemplate('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/InputGUIs/templates/tpl.matrix_input.html', TRUE, TRUE);

        $even = false;
        $tpl->setVariable('ROW_NAME', $this->getPostVar());
		foreach ($this->getScale() as $value => $title) {
			$tpl->setCurrentBlock('item');
			if ($this->getValue() == $value AND $this->getValue() !== NULL AND $this->getValue() !== '') {
				$tpl->setVariable('SELECTED', 'checked="checked"');
			}
            $tpl->setVariable('CLASS', $even ? "ilUnitEven":"ilUnitOdd");
            $even = !$even;
			$tpl->setVariable('VALUE', $value);
			$tpl->setVariable('NAME', $this->getPostVar());
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}


	/**
	 * @param $value array form $value[$postvar] = array(id, array(name, value))
	 */
	public function setValueByArray($value) {
		parent::setValueByArray($value);


		$post_var_parts = explode("[", str_replace("]", "", $this->getPostVar()));

		$value = $_POST;
		foreach ($post_var_parts as $part) {
			if (is_array($value) && array_key_exists($part,$value)) {
				$value = $value[$part];
			}else{
				return;
			}
		}

		$this->setValue($value);
	}


	/**
	 * @param array $scale
	 */
	public function setScale($scale) {
		$this->scale = $scale;
	}


	/**
	 * @return array
	 */
	public function getScale() {
		return $this->scale;
	}


	/**
	 * @param string $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}


	/**
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}


	/**
	 * @param array $values
	 */
	public function setValues($values) {
		$this->values = $values;
	}


	/**
	 * @return array
	 */
	public function getValues() {
		return $this->values;
	}

	/**
	 * Check input, strip slashes etc. set alert, if input is not ok.
	 * Todo: do some check here
	 *
	 * @return	boolean		Input ok, true/false
	 */
	function checkInput()
	{
		global $DIC;

		if($this->getRequired() ) {

			$post_var_parts = explode("[", str_replace("]", "", $this->getPostVar()));

			$value = $_POST;


			$pass = true;
			foreach ($post_var_parts as $part) {
				if (!is_array($value) || !array_key_exists($part,$value)) {
					$pass = false;
					$this->setAlert($DIC->language()->txt('msg_input_is_required'));

				}else{
					$value = $value[$part];
				}
			}

			if (!$pass || trim($value) == '') {
				$this->setAlert($DIC->language()->txt('msg_input_is_required'));
				return false;
			}
		}

		return true;
	}
}

?>