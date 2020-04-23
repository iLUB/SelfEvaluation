<?php

namespace ilub\plugin\SelfEvaluation\Question\Meta;

use ilSelfEvaluationPlugin;
use ilToolbarGUI;
use ilCtrl;
use ilPropertyFormGUI;
use ilObjSelfEvaluationGUI;
use ilGlobalPageTemplate;
use ilDBInterface;
use ilub\plugin\SelfEvaluation\Block\Meta\MetaBlock;
use ilAccess;
use ilub\plugin\SelfEvaluation\Question\Meta\Type\MetaTypeOption;
use ilUtil;
use ilub\plugin\SelfEvaluation\Question\Meta\Type\MetaQuestionType;
use ilTextInputGUI;
use ilub\plugin\SelfEvaluation\Question\Meta\Type\MetaTypeText;
use ilub\plugin\SelfEvaluation\Question\Meta\Type\MetaTypeSelect;
use ilub\plugin\SelfEvaluation\Question\Meta\Type\MetaTypeSingleChoice;
use ilub\plugin\SelfEvaluation\Question\Meta\Type\MetaTypeMatrix;
use ilRadioGroupInputGUI;

class MetaQuestionGUI
{

    const POSTVAR_PREFIX = 'mqst_';
    /**
     * @var ilSelfEvaluationPlugin
     */
    protected $plugin;

    /**
     * @var MetaBlock
     */
    protected $block;

    /**
     * @var ilPropertyFormGUI
     */
    protected $form;

    /**
     * @var ilGlobalPageTemplate
     */
    protected $tpl;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilObjSelfEvaluationGUI
     */
    protected $parent;

    /**
     * @var ilAccess
     */
    protected $access;
    /**
     * @var ilDBInterface
     */
    protected $db;

    public function __construct(
        ilDBInterface $db,
        ilObjSelfEvaluationGUI $parent,
        ilGlobalPageTemplate $tpl,
        ilCtrl $ilCtrl,
        ilToolbarGUI $ilToolbar,
        ilAccess $access,
        ilSelfEvaluationPlugin $plugin,
        MetaBlock $block
    ) {
        $this->tpl = $tpl;
        $this->toolbar = $ilToolbar;
        $this->ctrl = $ilCtrl;
        $this->parent = $parent;
        $this->plugin = $plugin;
        $this->access = $access;
        $this->db = $db;
        $this->block = $block;

    }

    public function executeCommand()
    {
        $this->ctrl->saveParameter($this, 'block_id');
        $this->performCommand();
    }

    public function performCommand()
    {
        $cmd = $this->ctrl->getCmd();

        if (!$this->access->checkAccess("read", $cmd, $this->parent->object->getRefId(), $this->plugin->getId(),
            $this->parent->object->getId())) {
            throw new \ilObjectException($this->plugin->txt("permission_denied"));
        }

        switch ($cmd) {
            case 'listFields':
            case 'saveFields':
            case 'confirmDeleteFields':
            case 'deleteFields':
            case 'addField':
            case 'saveField':
            case 'editField':
            case 'updateField':
            case 'saveSorting':
                $this->$cmd();
                break;
            default:
                $this->listFields();
                break;
        }

    }

    protected function listFields()
    {
        $this->toolbar->addButton('<b>&lt;&lt; ' . $this->plugin->txt('back_to_blocks') . '</b>',
            $this->ctrl->getLinkTargetByClass('ilSelfEvaluationListBlocksGUI', 'showContent'));
        $this->toolbar->addButton($this->lng->getTxtAddField(), $this->ctrl->getLinkTarget($this, 'addField'));

        $table = $this->createILubFieldDefinitionTableGUI();
        $table->parse($this->block->getFieldDefinitions());
        $this->tpl->setContent($table->getHTML());
        $this->tpl->addJavaScript($this->plugin->getDirectory() . '/templates/sortable.js');
    }

    /**
     * Save Field settings (currently only required status)
     */
    protected function saveFields()
    {
        foreach ($this->block->getFieldDefinitions() as $field) {
            $field->enableRequired((bool) isset($_POST['required'][$field->getId()]));
            $field->update();
        }

        global $lng;
        ilUtil::sendSuccess($lng->txt('saved_successfully'), true);
        $this->ctrl->redirect($this, 'listFields');
    }

    protected function confirmDeleteFields()
    {
        global $lng;
        $field_id = $_GET['field_id'];
        if (!count($field_id)) {
            ilUtil::sendFailure($lng->txt('select_one'));
            $this->listFields();

            return;
        }
        require_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setHeaderText($this->lng->getTxtConfirmDelete());

        $tmp_field = $this->block->getFactory()->createILubFieldDefinition($this->block->getId(), $field_id);
        $confirm->addItem('field_ids[]', $field_id, $tmp_field->getName());

        $confirm->setConfirm($lng->txt('delete'), 'deleteFields');
        $confirm->setCancel($lng->txt('cancel'), 'listFields');
        $this->tpl->setContent($confirm->getHTML());
    }

    /**
     * Delete selected fields
     */
    protected function deleteFields()
    {
        foreach ((array) $_POST['field_ids'] as $field_id) {
            $tmp_field = $this->block->getFactory()->createILubFieldDefinition($this->block->getId(),
                $field_id);
            $tmp_field->delete();
        }

        global $lng;
        ilUtil::sendSuccess($lng->txt('deleted'), true);
        $this->ctrl->redirect($this, 'listFields');
    }

    /**
     * Show field creation form
     * @return void
     */
    protected function addField()
    {
        $this->initFieldForm(self::MODE_CREATE);
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * Save a new field
     */
    protected function saveField()
    {
        $this->initFieldForm(self::MODE_CREATE);
        if ($this->form->checkInput()) {
            $field = $this->block->getFactory()->createILubFieldDefinition($this->block->getId());
            $field->setName($this->form->getInput('name'));
            $field->setShortTitle($this->form->getInput('short_title'));
            $field->setTypeId($this->form->getInput('type'));
            $field->setValues($this->getFormValuesByTypeId($field->getTypeId()));
            $field->enableRequired($this->form->getInput('required'));
            $field->save();
            $this->block->addFieldDefinition($field);

            ilUtil::sendSuccess($this->lng->getTxtFieldAdded(), true);
            $this->ctrl->redirect($this, 'listFields');
        }
        // not valid
        global $lng;
        ilUtil::sendFailure($lng->txt('err_check_input'));
        $this->form->setValuesByPost();
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * Edit one field
     * @return bool
     */
    protected function editField()
    {
        if (!$_REQUEST['field_id']) {
            $this->listFields();

            return;
        }

        $this->initFieldForm(self::MODE_UPDATE);

        $field = $this->block->getFactory()->createILubFieldDefinition($this->block->getId(),
            (int) $_REQUEST['field_id']);
        $item = $this->form->getItemByPostVar('name');
        $item->setValue($field->getName());
        $item = $this->form->getItemByPostVar('short_title');
        $item->setValue($field->getShortTitle());
        $item = $this->form->getItemByPostVar('type');
        $item->setValue($field->getTypeId());
        $this->setFormValuesByTypeId($field->getTypeId(), $field->getValues());
        $item = $this->form->getItemByPostVar('required');
        $item->setChecked($field->isRequired());

        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * Update field definition
     */
    protected function updateField()
    {
        global $lng;
        $this->initFieldForm(self::MODE_UPDATE);

        if ($this->form->checkInput()) {
            $field = $this->block->getFactory()->createILubFieldDefinition($this->block->getId(),
                (int) $_REQUEST['field_id']);
            $field->setName($this->form->getInput('name'));
            $field->setShortTitle($this->form->getInput('short_title'));

            $field->setTypeId($this->form->getInput('type'));
            $field->setValues($this->getFormValuesByTypeId($field->getTypeId()));
            $field->enableRequired($this->form->getInput('required'));
            $field->update();

            ilUtil::sendSuccess($lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'listFields');
        }

        ilUtil::sendFailure($lng->txt('err_check_input'));
        $this->form->setValuesByPost();
        $this->tpl->setContent($this->form->getHTML());
    }

    protected function saveSorting()
    {
        foreach ($_POST['position'] as $position => $field_id) {
            $field = $this->block->getFactory()->createILubFieldDefinition($this->block->getId(), $field_id);
            $field->setPosition($position + 1);
            $field->update();
        }

        ilUtil::sendSuccess($this->lng->getSortingSaved(), true);
        $this->ctrl->redirect($this, 'listFields');
    }

    protected function createILubFieldDefinitionTableGUI() : MetaQuestionTableGUI
    {
        $table = new MetaQuestionTableGUI($this, 'listFields', $this->types, $this->hasSorting());
        $table->setTitle($this->block_title . ': ' . $this->plugin->txt('question_table_title'));
        return $table;
    }

    /**
     * Init/create property form for fields
     * @param $mode
     */
    protected function initFieldForm($mode)
    {
        if ($this->form instanceof ilPropertyFormGUI) {
            return;
        }
        global $lng;
        require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
        $this->form = new ilPropertyFormGUI();

        switch ($mode) {
            case self::MODE_CREATE:
                $this->form->setFormAction($this->ctrl->getFormAction($this));
                $this->form->setTitle($lng->txt('add'));
                $this->form->addCommandButton('saveField', $lng->txt('save'));
                $this->form->addCommandButton('listFields', $lng->txt('cancel'));
                break;

            case self::MODE_UPDATE:
                $this->ctrl->setParameter($this, 'field_id', (int) $_REQUEST['field_id']);
                $this->form->setFormAction($this->ctrl->getFormAction($this));
                $this->form->setTitle($lng->txt('edit'));
                $this->form->addCommandButton('updateField', $lng->txt('save'));
                $this->form->addCommandButton('listFields', $lng->txt('cancel'));
                break;
        }

        // Name
        $na = new ilTextInputGUI($lng->txt('name'), 'name');
        $na->setSize(32);
        $na->setMaxLength(255);
        $na->setRequired(true);
        $this->form->addItem($na);

        // Name
        $na = new ilTextInputGUI($lng->txt('short_inst_name'), 'short_title');
        $na->setSize(32);
        $na->setMaxLength(255);
        $na->setRequired(true);
        $this->form->addItem($na);

        // Type
        $ty = new ilRadioGroupInputGUI($lng->txt('type'), 'type');
        $ty->setRequired(true);
        $this->form->addItem($ty);

        foreach ($this->types as $type) {
            $option = $type->getValueDefinitionInputGUI(new MetaQuestionTypeOption());
            $option->setTitle($type->getTypeName());
            $option->setValue($type->getId());
            $ty->addOption($option);
        }

        // Required
        $re = new ilCheckboxInputGUI($lng->txt('required_field'), 'required');
        $re->setValue(1);
        $this->form->addItem($re);
    }

    /**
     * @param iLubFieldDefinition $field
     * @return \ilFormPropertyGUI
     */
    protected function getPresentationInputGuiByTypeId($field)
    {
        $type = MetaQuestionType::getTypeByTypeId($field->getTypeId(), $this->types);
        if ($type) {
            return $type->getPresentationInputGUI($field->getName(), 'field_def_' . $field->getId(),
                $field->getValues());
        }

        return false;
    }

    /**
     * @param int $type_id
     * @return MetaTypeOption|false
     */
    protected function getValueDefinitionInputGuiByTypeId($type_id)
    {
        /** @var ilRadioGroupInputGUI $group */
        $group = $this->form->getItemByPostVar('type');
        $options = $group->getOptions();
        if (is_array($options)) {
            /** @var MetaQuestionTypeOption[] $options */
            foreach ($options as $option) {
                if ($option->getValue() == $type_id) {

                    return $option;
                }
            }
        }

        return false;
    }

    /**
     * @param int $type_id
     * @return array
     */
    protected function getFormValuesByTypeId($type_id)
    {
        $type = MetaQuestionType::getTypeByTypeId($type_id, $this->types);

        if (!$type instanceof MetaQuestionType) {

            return [];
        }

        $post_values = $type->getValues($this->form);
        if (!is_array($post_values)) {

            return [];
        }

        $values = [];
        foreach ($post_values as $key => $value) {
            $value = trim(ilUtil::stripSlashes($value));
            if (strlen($value)) {
                $values[$key] = $value;
            }
        }

        return $values;

    }

    protected function setFormValuesByTypeId(int $type_id, array $values)
    {
        $item = $this->getValueDefinitionInputGuiByTypeId($type_id);
        $type = MetaQuestionType::getTypeByTypeId($type_id, $this->getTypes());
        if ($type AND $item) {
            $type->setValues($item, $values);
        }
    }

    public function enableSorting(bool $enable_sorting)
    {
        $this->enable_sorting = $enable_sorting;
    }

    public function hasSorting() : bool
    {
        return $this->enable_sorting;
    }

    /**
     * @param MetaQuestionType[] $types
     */
    public function setTypes(array $types)
    {
        $this->types = $types;
    }

    /**
     * @return MetaQuestionType[]
     */
    public function getTypes() : array
    {
        return $this->types;
    }

    static function getTypesArray()
    {
        $types[] = new MetaTypeText();
        $types[] = new MetaTypeSelect();
        $types[] = new MetaTypeSingleChoice();
        $types[] = new MetaTypeMatrix();

        return $types;
    }
} 