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

require_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
 * Class iLubFieldDefinitionTableGUI
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class iLubFieldDefinitionTableGUI extends ilTable2GUI
{

    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilLanguage
     */
    protected $lng;
    /**
     * @var iLubFieldDefinitionType[]
     */
    protected $types;
    /**
     * @var bool
     */
    protected $sortable;

    /**
     * @param                           $a_parent_obj
     * @param string                    $a_parent_cmd
     * @param iLubFieldDefinitionType[] $types
     * @param bool                      $sortable
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $types, $sortable)
    {
        /** @var ilCtrl $ilCtrl */
        global $ilCtrl, $lng, $tpl;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;

        $this->types = $types;
        $this->sortable = $sortable;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), $this->getParentCmd()));

        if ($sortable) {
            $tpl->addJavaScript('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/iLubFieldDefinition/js/sortable.js');
            $this->addColumn('', 'position', '20px');
            $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
            $lng->loadLanguageModule('content');
            $this->addMultiCommand('saveSorting', $this->lng->txt('cont_save_positions'));
            $column_sorting = false;
        } else {
            $column_sorting = true;
            $this->setDefaultOrderField('name');
            $this->setDefaultOrderDirection('asc');
        }
        $this->addColumn($this->lng->txt('name'), $column_sorting ? 'name' : false, 'auto');
        $this->addColumn($this->lng->txt('short_inst_name'), $column_sorting ? 'short_title' : false, 'short_title');
        $this->addColumn($this->lng->txt('type'), $column_sorting ? 'type' : false, 'auto');
        $this->addColumn($this->lng->txt('required_field'), '', 'auto');
        $this->addColumn($this->lng->txt('actions'), '', 'auto');

        $this->addCommandButton('saveFields', $this->lng->txt('save'));

        $this->setSelectAllCheckbox('field_ids[]');

        $this->enable('sort');
        $this->enable('header');
        $this->enable('numinfo');
        $this->enable('select_all');

        $this->setRowTemplate('tpl.field_def_data_table_row.html',
            'Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/iLubFieldDefinition/');
    }

    /**
     * Fill row
     * @param array $row
     */
    public function fillRow($row)
    {
        if ($this->sortable) {
            $this->tpl->setCurrentBlock('sortable');
            $this->tpl->setVariable('POSITION_ID', $row['field_id']);
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setVariable('VAL_ID', $row['field_id']);
        $this->tpl->setVariable('VAL_NAME', $row['name']);
        $this->tpl->setVariable('VAL_SHORT_TITLE', $row['short_title']);

        $this->tpl->setVariable('VAL_TYPE', $row['type']);
        $this->tpl->setVariable('REQUIRED_CHECKED', $row['required'] ? 'checked="checked"' : '');

        // actions
        $this->ctrl->setParameter($this->getParentObject(), 'field_id', $row['field_id']);
        $ac = new ilAdvancedSelectionListGUI();
        $ac->setId('field_' . $row['field_id']);
        $ac->setListTitle($this->lng->txt('actions'));

        $edit_link = $this->ctrl->getLinkTarget($this->getParentObject(), 'editField');
        $ac->addItem($this->lng->txt('edit'), 'edit_field', $edit_link);

        $delete_link = $this->ctrl->getLinkTarget($this->getParentObject(), 'confirmDeleteFields');
        $ac->addItem($this->lng->txt('delete'), 'delete_field', $delete_link);

        $this->tpl->setVariable('ACTIONS', $ac->getHTML());
    }

    /**
     * Parse table data
     * @param ilubFieldDefinition[] $field_definitions
     */
    public function parse($field_definitions)
    {
        $rows = [];
        if (count($field_definitions) > 0) {
            foreach ($field_definitions as $field) {
                $item = [];
                $item['field_id'] = $field->getId();
                $item['name'] = $field->getName();
                $item['short_title'] = $field->getShortTitle();

                $type = iLubFieldDefinitionType::getTypeByTypeId($field->getTypeId(), $this->types);
                if ($type instanceof iLubFieldDefinitionType) {
                    $item['type'] = $type->getTypeName();
                }

                $item['required'] = (bool) $field->isRequired();
                $rows[] = $item;
            }
        }
        $this->setData($rows);
        if (!sizeof($rows)) {
            $this->clearCommandButtons();
        }
    }
}