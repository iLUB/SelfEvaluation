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

/**
 * Class iLubFieldDefinitionContainerGUI
 *
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
abstract class iLubFieldDefinitionContainerGUI {

	const MODE_CREATE = 1;
	const MODE_UPDATE = 2;
	/**
	 * @var ilPropertyFormGUI
	 */
	protected $form;
	/**
	 * @var iLubFieldDefinitionLng
	 */
	protected $lng;
	/**
	 * @var int
	 */
	protected $ref_id;
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs_gui;
	/**
	 * @var iLubFieldDefinitionContainer
	 */
	protected $container;
	/**
	 * @var iLubFieldDefinitionType[]
	 */
	protected $types = array();
	/**
	 * @var bool
	 */
	protected $enable_sorting = TRUE;


	/**
	 * @param iLubFieldDefinitionContainer $container
	 * @param iLubFieldDefinitionType[]    $types
	 * @param iLubFieldDefinitionLng       $lng
	 * @param int                          $ref_id a reference id to which the user must have write access
	 */
	public function __construct(iLubFieldDefinitionContainer $container, $types, iLubFieldDefinitionLng $lng, $ref_id) {
		global $tpl, $ilCtrl, $ilTabs;
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->tabs_gui = $ilTabs;

		$this->container = $container;
		$this->types = array_unique($types);
		$this->lng = $lng;
		$this->ref_id = $ref_id;
	}


	/**
	 * Execute Command
	 */
	public function executeCommand() {
		/**
		 * @var ilAccessHandler $ilAccess
		 */
		global $ilAccess, $lng;

		if (! $ilAccess->checkAccess('write', '', $this->ref_id)) {
			ilUtil::sendFailure($lng->txt('permission_denied'), TRUE);
			$this->ctrl->clearParametersByClass('ilRepositoryGUI');
			$this->ctrl->redirectByClass('ilRepositoryGUI');
		}

		$cmd = $this->ctrl->getCmd();

//		$next_class = $this->ctrl->getNextClass($this);
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


	/**
	 * List existing custom fields
	 */
	protected function listFields() {
		/** @var ilToolbarGUI $ilToolbar */
		global $ilToolbar;

		$ilToolbar->addButton($this->lng->getTxtAddField(), $this->ctrl->getLinkTarget($this, 'addField'));

		$table = $this->createILubFieldDefinitionTableGUI();
		$table->parse($this->container->getFieldDefinitions());
		$this->tpl->setContent($table->getHTML());
	}


	/**
	 * Save Field settings (currently only required status)
	 */
	protected function saveFields() {
		foreach ($this->container->getFieldDefinitions() as $field) {
			$field->enableRequired((bool)isset($_POST['required'][$field->getId()]));
			$field->update();
		}

		global $lng;
		ilUtil::sendSuccess($lng->txt('saved_successfully'), TRUE);
		$this->ctrl->redirect($this, 'listFields');
	}


	protected function confirmDeleteFields() {
		global $lng;
		$field_id = $_GET['field_id'];
		if (! count($field_id)) {
			ilUtil::sendFailure($lng->txt('select_one'));
			$this->listFields();

			return;
		}
		require_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($this->ctrl->getFormAction($this));
		$confirm->setHeaderText($this->lng->getTxtConfirmDelete());

		$tmp_field = $this->container->getFactory()->createILubFieldDefinition($this->container->getId(), $field_id);
		$confirm->addItem('field_ids[]', $field_id, $tmp_field->getName());

		$confirm->setConfirm($lng->txt('delete'), 'deleteFields');
		$confirm->setCancel($lng->txt('cancel'), 'listFields');
		$this->tpl->setContent($confirm->getHTML());
	}

	/**
	 * Delete selected fields
	 */
	protected function deleteFields() {
		foreach ((array)$_POST['field_ids'] as $field_id) {
			$tmp_field = $this->container->getFactory()->createILubFieldDefinition($this->container->getId(), $field_id);
			$tmp_field->delete();
		}

		global $lng;
		ilUtil::sendSuccess($lng->txt('deleted'), TRUE);
		$this->ctrl->redirect($this, 'listFields');
	}


	/**
	 * Show field creation form
	 *
	 * @return void
	 */
	protected function addField() {
		$this->initFieldForm(self::MODE_CREATE);
		$this->tpl->setContent($this->form->getHTML());
	}


	/**
	 * Save a new field
	 */
	protected function saveField() {
		$this->initFieldForm(self::MODE_CREATE);
		if ($this->form->checkInput()) {
			$field = $this->container->getFactory()->createILubFieldDefinition($this->container->getId());
			$field->setName($this->form->getInput('name'));
			$field->setShortTitle($this->form->getInput('short_title'));
			$field->setTypeId($this->form->getInput('type'));
			$field->setValues($this->getFormValuesByTypeId($field->getTypeId()));
			$field->enableRequired($this->form->getInput('required'));
			$field->save();
			$this->container->addFieldDefinition($field);

			ilUtil::sendSuccess($this->lng->getTxtFieldAdded(), TRUE);
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
	 *
	 * @return bool
	 */
	protected function editField() {
		if (! $_REQUEST['field_id']) {
			$this->listFields();

			return;
		}

		$this->initFieldForm(self::MODE_UPDATE);

		$field = $this->container->getFactory()->createILubFieldDefinition($this->container->getId(),
			(int)$_REQUEST['field_id']);
		/** @var ilTextInputGUI $item */
		$item = $this->form->getItemByPostVar('name');
		$item->setValue($field->getName());
		$item = $this->form->getItemByPostVar('short_title');
		$item->setValue($field->getShortTitle());
		/** @var ilRadioGroupInputGUI $item */
		$item = $this->form->getItemByPostVar('type');
		$item->setValue($field->getTypeId());
		$this->setFormValuesByTypeId($field->getTypeId(), $field->getValues());
		/** @var ilCheckboxInputGUI $item */
		$item = $this->form->getItemByPostVar('required');
		$item->setChecked($field->isRequired());

		$this->tpl->setContent($this->form->getHTML());
	}


	/**
	 * Update field definition
	 */
	protected function updateField() {
		global $lng;
		$this->initFieldForm(self::MODE_UPDATE);

		if ($this->form->checkInput()) {
			$field = $this->container->getFactory()->createILubFieldDefinition($this->container->getId(),
				(int)$_REQUEST['field_id']);
			$field->setName($this->form->getInput('name'));
			$field->setShortTitle($this->form->getInput('short_title'));

			$field->setTypeId($this->form->getInput('type'));
			$field->setValues($this->getFormValuesByTypeId($field->getTypeId()));
			$field->enableRequired($this->form->getInput('required'));
			$field->update();

			ilUtil::sendSuccess($lng->txt('settings_saved'), TRUE);
			$this->ctrl->redirect($this, 'listFields');
		}

		ilUtil::sendFailure($lng->txt('err_check_input'));
		$this->form->setValuesByPost();
		$this->tpl->setContent($this->form->getHTML());
	}


	protected  function saveSorting() {
		foreach ($_POST['position'] as $position => $field_id) {
			$field = $this->container->getFactory()->createILubFieldDefinition($this->container->getId(), $field_id);
			$field->setPosition($position + 1);
			$field->update();
		}

		ilUtil::sendSuccess($this->lng->getSortingSaved(), TRUE);
		$this->ctrl->redirect($this, 'listFields');
	}


	/**
	 * Init/create property form for fields
	 *
	 * @param $mode
	 */
	protected function initFieldForm($mode) {
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
				$this->ctrl->setParameter($this, 'field_id', (int)$_REQUEST['field_id']);
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
			$option = $type->getValueDefinitionInputGUI(new iLubFieldDefinitionTypeOption());
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
	 *
	 * @return \ilFormPropertyGUI
	 */
	protected function getPresentationInputGuiByTypeId($field) {
		$type = iLubFieldDefinitionType::getTypeByTypeId($field->getTypeId(), $this->types);
		if ($type) {
			return $type->getPresentationInputGUI($field->getName(), 'field_def_' . $field->getId(), $field->getValues());
		}

		return false;
	}


	/**
	 * @param int $type_id
	 *
	 * @return \iLubFieldDefinitionTypeOption|false
	 */
	protected function getValueDefinitionInputGuiByTypeId($type_id) {
		/** @var ilRadioGroupInputGUI $group */
		$group = $this->form->getItemByPostVar('type');
		$options = $group->getOptions();
		if (is_array($options)) {
			/** @var iLubFieldDefinitionTypeOption[] $options */
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
	 *
	 * @return array
	 */
	protected function getFormValuesByTypeId($type_id) {
		$type = iLubFieldDefinitionType::getTypeByTypeId($type_id, $this->types);

		if (! $type instanceof iLubFieldDefinitionType) {

			return array();
		}

		$post_values = $type->getValues($this->form);
		if (! is_array($post_values)) {

			return array();
		}

		$values = array();
		foreach ($post_values as $key => $value) {
			$value = trim(ilUtil::stripSlashes($value));
			if (strlen($value)) {
				$values[$key] = $value;
			}
		}


		return $values;

	}


	/**
	 * @param int   $type_id
	 * @param array $values
	 */
	protected function setFormValuesByTypeId($type_id, $values) {
		$item = $this->getValueDefinitionInputGuiByTypeId($type_id);
		$type = iLubFieldDefinitionType::getTypeByTypeId($type_id, $this->types);
		if ($type AND $item) {
			$type->setValues($item, $values);
		}
	}


	/**
	 * @param boolean $enable_sorting
	 */
	public function enableSorting($enable_sorting) {
		$this->enable_sorting = $enable_sorting;
	}


	/**
	 * @return boolean
	 */
	public function hasSorting() {
		return $this->enable_sorting;
	}


	/**
	 * @return iLubFieldDefinitionTableGUI
	 */
	protected function createILubFieldDefinitionTableGUI() {
		require_once('class.iLubFieldDefinitionTableGUI.php');
		$table = new iLubFieldDefinitionTableGUI($this, 'listFields', $this->types, $this->hasSorting());

		return $table;
	}

    /**
     * @param \iLubFieldDefinitionType[] $types
     */
    public function setTypes($types)
    {
        $this->types = $types;
    }

    /**
     * @return \iLubFieldDefinitionType[]
     */
    public function getTypes()
    {
        return $this->types;
    }


}