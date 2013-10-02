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
class ilSliderInputGUI extends ilCustomInputGUI {

	const PREFIX = 'fsx_';
	/**
	 * @var array
	 */
	protected $values = array( 0, 1 );
	/**
	 * @var int
	 */
	protected $min = 0;
	/**
	 * @var int
	 */
	protected $max = 0;
	/**
	 * @var string
	 */
	protected $unit = '%';
	/**
	 * @var string
	 */
	protected $ajax = '';


	public function __construct($title, $post_var, $min, $max, $ajax_request = false) {
		parent::__construct($title, $post_var);
		$this->setMin($min);
		$this->setMax($max);
		$this->setAjax($ajax_request);
	}


	public function getHtml() {
		return $this->buildHTML();
	}


	private function buildHTML() {
		$pl = new ilSelfEvaluationPlugin();
		$tpl = $pl->getTemplate('default/tpl.slider_input.html');
		$values = $this->getValues();
		$tpl->setVariable('VAL_FROM', $values[0]);
		$tpl->setVariable('VAL_TO', $values[1]);
		$tpl->setVariable('MIN', $this->getMin());
		$tpl->setVariable('MAX', $this->getMax());
		$tpl->setVariable('POSTVAR', self::PREFIX . $this->getPostVar() . '');
		$tpl->setVariable('UNIT', $this->getUnit());
		if ($this->getAjax()) {
			$tpl->setVariable('AJAX', $this->getAjax());
			$tpl->setVariable('WARNING', $pl->txt('warning_overlap'));
		}

		return $tpl->get();
	}


	/**
	 * @return bool
	 */
	public function checkInput() {
		global $lng;
		$_POST[$this->getPostVar()] = array(
			$_POST[self::PREFIX . $this->getPostVar() . '_from'],
			$_POST[self::PREFIX . $this->getPostVar() . '_to']
		);
		if ($this->getRequired() AND
			trim($_POST[self::PREFIX . $this->getPostVar() . '_from']) == '' AND
			trim($_POST[self::PREFIX . $this->getPostVar() . '_to']) == ''
		) {
			$this->setAlert($lng->txt('msg_input_is_required'));

			return false;
		}

		return $this->checkSubItemsInput();
	}


	public function setValueByArray($array) {
		parent::setValueByArray($array);
		$this->setValues($array[$this->getPostVar()]);
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
	 * @return boolean
	 */
	public function getDisableOldFields() {
		return $this->disableOldFields;
	}


	/**
	 * @param int $max
	 */
	public function setMax($max) {
		$this->max = $max;
	}


	/**
	 * @return int
	 */
	public function getMax() {
		return $this->max;
	}


	/**
	 * @param int $min
	 */
	public function setMin($min) {
		$this->min = $min;
	}


	/**
	 * @return int
	 */
	public function getMin() {
		return $this->min;
	}


	/**
	 * @param string $unit
	 */
	public function setUnit($unit) {
		$this->unit = $unit;
	}


	/**
	 * @return string
	 */
	public function getUnit() {
		return $this->unit;
	}


	/**
	 * @param mixed $ajax
	 */
	public function setAjax($ajax) {
		$this->ajax = $ajax;
	}


	/**
	 * @return mixed
	 */
	public function getAjax() {
		return $this->ajax;
	}
}

?>