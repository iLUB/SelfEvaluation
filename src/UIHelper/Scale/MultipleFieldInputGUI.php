<?php
namespace ilub\plugin\SelfEvaluation\UIHelper\Scale;

use ilSubEnabledFormPropertyGUI;
use ilRepositoryObjectPlugin;
use ilTemplate;
use ilUtil;

class MultipleFieldInputGUI extends ilSubEnabledFormPropertyGUI
{

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
     * @var ilRepositoryObjectPlugin
     */
    protected $plugin;

    public function __construct(ilRepositoryObjectPlugin $plugin, string $title, string $post_var, string $field_name)
    {
        parent::__construct($title, $post_var);
        $this->setFieldName($field_name);
        $this->plugin = $plugin;
    }

    public function getHtml() : string
    {
        $tpl = $this->plugin->getTemplate('default/Form/tpl.multiple_input.html', true, true);
        $tpl->setVariable('LOCK_CSS', $this->getDisabled() ? 'locked' : '');
        if ($this->getDisabled()) {
            $this->setInfo($this->plugin->txt('locked'));
        }
        if (count($this->getValues()) > 0) {
            foreach ($this->getValues() as $id => $value) {
                $tpl->setCurrentBlock('input');
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
        if (!$this->getDisabled()) {
            $tpl->setCurrentBlock('new_input');
            $tpl->setVariable('VALUE_N_NEW', $this->getFieldName() . '_new[value][]');
            $tpl->setVariable('TITLE_N_NEW', $this->getFieldName() . '_new[title][]');
            $tpl->setVariable('DISABLED_N', $this->getDisabled() ? 'disabled' : '');
            $tpl->setVariable('PLACEHOLDER_VALUE', $this->getPlaceholderValue());
            $tpl->setVariable('PLACEHOLDER_TITLE', $this->getPlaceholderTitle());
            $tpl->setVariable('LOCK_CSS', $this->getDisabled() ? 'locked' : '');
            $tpl->parseCurrentBlock();
            $tpl->setVariable("DESCRIPTION", $this->getDescription());
        }

        return $tpl->get();
    }

    public function insert(ilTemplate $a_tpl)
    {
        $a_tpl->setCurrentBlock("prop_custom");
        $a_tpl->setVariable("CUSTOM_CONTENT", $this->getHtml());
        $a_tpl->parseCurrentBlock();
    }

    public function setValueByArray(array $value)
    {
        foreach ($this->getSubItems() as $item) {
            /**
             * @var self $item
             */
            $item->setValueByArray($value);
        }
        $this->setValues(is_array($value[$this->getPostVar()]) ? $value[$this->getPostVar()] : []);
    }

    public function checkInput()
    {
        $lng = $this->lng;

        if ($this->getPostVar()) {
            $_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
            if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "") {
                $this->setAlert($lng->txt("msg_input_is_required"));
                return false;
            }
        }
        return $this->checkSubItemsInput();
    }

    public function setValues(array $values)
    {
        $this->values = $values;
    }

    public function getValues() : array
    {
        return $this->values;
    }

    public function setFieldName(string $field_name)
    {
        $this->field_name = $field_name;
    }

    public function getFieldName() : string
    {
        return $this->field_name;
    }

    public function setPlaceholderTitle(string $placeholder_title)
    {
        $this->placeholder_title = $placeholder_title;
    }

    public function getPlaceholderTitle() : string
    {
        return $this->placeholder_title;
    }

    public function setPlaceholderValue(string $placeholder_value)
    {
        $this->placeholder_value = $placeholder_value;
    }

    public function getPlaceholderValue() : string
    {
        return $this->placeholder_value;
    }

    public function setDefaultValue(string $default_value)
    {
        $this->default_value = $default_value;
    }

    public function getDefaultValue() : string
    {
        return $this->default_value;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function getDescription() : string
    {
        return $this->description;
    }
}

