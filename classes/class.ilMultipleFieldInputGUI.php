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
class ilMultipleFieldInputGUI extends ilCustomInputGUI {

	/**
	 * @var array
	 */
	protected $values;
	/**
	 * @var string
	 */
	protected $placeholder;
	/**
	 * @var bool
	 */
	protected $disableOldFields;


	public function __construct($title, $post_var, $placeholder) {
		parent::__construct($title, $post_var);
		$this->placeholder = $placeholder;
	}


	public function getHtml() {
		return $this->buildHTML();
	}


	private function buildHTML() {
		$pl = new ilSelfEvaluationPlugin();
		$tpl = $pl->getTemplate('default/tpl.multiple_input.html');
		$tpl->setVariable('ADD_BUTTON', $pl->getDirectory() . '/templates/images/edit_add.png');
		foreach ($this->values as $id => $value) {
			$tpl->setCurrentBlock('input');
			$tpl->setVariable('VALUE_N', $this->placeholder . '_old[value][' . $id . ']');
			$tpl->setVariable('VALUE_V', $value['value']);
			$tpl->setVariable('TITLE_N', $this->placeholder . '_old[title][' . $id . ']');
			$tpl->setVariable('TITLE_V', $value['title']);
			$tpl->setVariable('DISABLED', $this->getDisabled() ? 'disabled' : '');
			$tpl->parseCurrentBlock();
		}
		$tpl->setCurrentBlock('new_input');
		$tpl->setVariable('VALUE_N_NEW', $this->placeholder . '_new[value][]');
		$tpl->setVariable('TITLE_N_NEW', $this->placeholder . '_new[title][]');
		$tpl->setVariable('DISABLED_N', $this->getDisabled() ? 'disabled' : '');
		$tpl->parseCurrentBlock();

		return $tpl->get();
	}


	/**
	 * @param $value array form $value[$postvar] = array(id, array(name, value))
	 */
	public function setValueByArray($value) {
		parent::setValueByArray($value);
		$this->values = is_array($value[$this->getPostVar()]) ? $value[$this->getPostVar()] : array();
	}


	/**
	 * @param boolean $disableOldFields
	 */
	public function setDisableOldFields($disableOldFields) {
		$this->disableOldFields = $disableOldFields;
	}


	/**
	 * @return boolean
	 */
	public function getDisableOldFields() {
		return $this->disableOldFields;
	}
}

?>