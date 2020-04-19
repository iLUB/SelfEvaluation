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
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class iLubFieldDefinitionTypeSelect extends iLubFieldDefinitionType
{

    const TYPE_ID = 2;

    /**
     * @return int
     */
    public function getId()
    {
        return self::TYPE_ID;
    }

    /**
     * Return a title in the users translation
     * @return string
     */
    public function getTypeName()
    {
        global $lng;
        $lng->loadLanguageModule('ps');

        return $lng->txt('ps_type_select_long');
    }

    /**
     * @param iLubFieldDefinitionTypeOption $option
     * @return iLubFieldDefinitionTypeOption
     */
    public function getValueDefinitionInputGUI(iLubFieldDefinitionTypeOption &$option)
    {
        global $lng;

        // Select Type Values
        require_once('Services/Form/classes/class.ilTextWizardInputGUI.php');
        $ty_se_mu = new ilTextWizardInputGUI($lng->txt('value'), 'value_' . $this->getId());
        $ty_se_mu->setRequired(true);
        $ty_se_mu->setSize(32);
        $ty_se_mu->setMaxLength(128);
        $ty_se_mu->setValues(['']);
        $option->addSubItem($ty_se_mu);

        return $option;
    }

    /**
     * @param iLubFieldDefinitionTypeOption $item
     * @param array                         $values
     */
    public function setValues(iLubFieldDefinitionTypeOption $item, $values = [])
    {
        foreach ($item->getSubItems() as $sub_item) {
            if ($sub_item instanceof ilTextWizardInputGUI AND $sub_item->getPostVar() == 'value_' . $this->getId()) {
                $sub_item->setValue($values);
            }
        }
    }

    /**
     * @param ilPropertyFormGUI $form
     * @return array
     */
    public function getValues(ilPropertyFormGUI $form)
    {
        return $form->getInput('value_' . $this->getId());
    }

    /**
     * @param string $title
     * @param string $postvar
     * @param array  $values
     * @return ilFormPropertyGUI
     */
    public function getPresentationInputGUI($title, $postvar, $values)
    {
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/InputGUIs/classes/class.ilSelectInputGUIwithDefaultText.php');
        $select = new ilSelectInputGUIwithDefaultText($title, $postvar);

        $options = [];
        foreach ($values as $key => $value) {
            $options[$key] = $value;
        }
        $select->setOptions($options);

        return $select;
    }
}