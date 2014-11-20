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
	/**
	 * @var string
	 */
	protected $question = '';


	public function getHtml() {
		return $this->buildHTML();
	}


	/**
	 * @return string
	 */
	private function buildHTML() {
		$pl = new ilSelfEvaluationPlugin();
		$tpl = $pl->getTemplate('default/Form/tpl.matrix_input.html');
		$css = ((count($this->getScale()) % 2) == 1) ? 1 : 2;
		$tpl->setVariable('QUESTION', $this->getQuestion());
		$width = floor(100 / count($this->getScale()));
		foreach ($this->getScale() as $value => $title) {
			$tpl->setCurrentBlock('item');
			if ($this->getValue() == $value AND $this->getValue() !== NULL AND $this->getValue() !== '') {
				$tpl->setVariable('SELECTED', 'checked="checked"');
			}
			$tpl->setVariable('CSS', 'col' . $css);
			$tpl->setVariable('STYLE', $width . '%');
			$tpl->setVariable('VALUE', $value);
			$tpl->setVariable('NAME', $this->getPostVar());
			$css = $css == 1 ? 2 : 1;
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}


	/**
	 * @param $value array form $value[$postvar] = array(id, array(name, value))
	 */
	public function setValueByArray($value) {
		parent::setValueByArray($value);
		$this->setValue($value[$this->getPostVar()]);
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
	 * @param string $question
	 */
	public function setQuestion($question) {
		$this->question = $question;
	}


	/**
	 * @return string
	 */
	public function getQuestion() {
		return $this->question;
	}
}

?>