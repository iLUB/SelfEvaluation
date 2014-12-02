<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs
/LICENSE */
include_once('./Services/Form/classes/class.ilCustomInputGUI.php');
require_once('./Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php');
/**
 * Class ilMultipleTextInputGUI
 *
 * @author  Fabian Schmid <fabian.schmid@ilub.unibe.ch>
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
	protected $field_name;
	/**
	 * @var string
	 */
	protected $placeholder_value = "Value";
	/**
	 * @var string
	 */
	protected $placeholder_title = 'Title';
    /**
     * @var int
     */
    protected $default_value = 0;

    /**
     * @var string
     */
    protected $description = "";




	/**
	 * @param string $title
	 * @param string $post_var
	 * @param        $field_name
	 */
	public function __construct($title, $post_var, $field_name) {
		parent::__construct($title, $post_var);
		$this->setFieldName($field_name);
	}


	/**
	 * @return string
	 */
	public function getHtml() {
		$pl = new ilSelfEvaluationPlugin();
		$tpl = $pl->getTemplate('default/Form/tpl.multiple_input.html', true, true);
		$tpl->setVariable('LOCK_CSS', $this->getDisabled() ? 'locked' : '');
		if ($this->getDisabled()) {
			$this->setInfo($pl->txt('locked'));
		}
		if (count($this->getValues()) > 0) {
			foreach ($this->getValues() as $id => $value) {
				$tpl->setCurrentBlock('input');
				$tpl->setVariable('DELETE_BUTTON', $pl->getDirectory() . '/templates/images/edit_remove.png');
				$tpl->setVariable('VALUE_N', $this->getFieldName() . '_old[value][' . $id . ']');
				$tpl->setVariable('VALUE_V', $value['value']);
				$tpl->setVariable('TITLE_N', $this->getFieldName() . '_old[title][' . $id . ']');
				$tpl->setVariable('TITLE_V', $value['title']);
				$tpl->setVariable('DISABLED', $this->getDisabled() ? 'disabled' : '');
				$tpl->setVariable('POSTVAR', $this->getPostVar());
				$tpl->setVariable('LOCK_CSS', $this->getDisabled() ? 'locked' : '');
				$tpl->setVariable('ID', $id);
				$tpl->parseCurrentBlock();
			}
		}
		if (! $this->getDisabled()) {
			$tpl->setCurrentBlock('new_input');
			$tpl->setVariable('ADD_BUTTON', $pl->getDirectory() . '/templates/images/edit_add.png');
			$tpl->setVariable('VALUE_N_NEW', $this->getFieldName() . '_new[value][]');
			$tpl->setVariable('TITLE_N_NEW', $this->getFieldName() . '_new[title][]');
			$tpl->setVariable('DISABLED_N', $this->getDisabled() ? 'disabled' : '');
			$tpl->setVariable('PLACEHOLDER_VALUE', $this->getPlaceholderValue());
//            $tpl->setVariable('DEFAULT_VALUE', $this->getDefaultValue());
			$tpl->setVariable('PLACEHOLDER_TITLE', $this->getPlaceholderTitle());
			$tpl->setVariable('LOCK_CSS', $this->getDisabled() ? 'locked' : '');
			$tpl->parseCurrentBlock();
            $tpl->setVariable("DESCRIPTION", $this->getDescription());
		}

		return $tpl->get();
	}


	/**
	 * @param $value array form $value[$postvar] = array(id, array(name, value))
	 */
	public function setValueByArray($value) {
		parent::setValueByArray($value);
		$this->setValues(is_array($value[$this->getPostVar()]) ? $value[$this->getPostVar()] : array());
	}


	//
	// Setter&Getter
	//
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
	 * @param string $field_name
	 */
	public function setFieldName($field_name) {
		$this->field_name = $field_name;
	}


	/**
	 * @return string
	 */
	public function getFieldName() {
		return $this->field_name;
	}


	/**
	 * @param string $placeholder_title
	 */
	public function setPlaceholderTitle($placeholder_title) {
		$this->placeholder_title = $placeholder_title;
	}


	/**
	 * @return string
	 */
	public function getPlaceholderTitle() {
		return $this->placeholder_title;
	}


	/**
	 * @param string $placeholder_value
	 */
	public function setPlaceholderValue($placeholder_value) {
		$this->placeholder_value = $placeholder_value;
	}


	/**
	 * @return string
	 */
	public function getPlaceholderValue() {
		return $this->placeholder_value;
	}

    /**
     * @param string $default_value
     */
    public function setDefaultValue($default_value)
    {
        $this->default_value = $default_value;
    }

    /**
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->default_value;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}

?>