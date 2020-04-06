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
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/iLubFieldDefinition/classes/types/class.iLubFieldDefinitionTypeOption.php');

/**
 * Class iLubFieldDefinitionType
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
abstract class iLubFieldDefinitionType
{

    /**
     * Make sure the type id is unique (at least within the container)
     * @return int
     */
    abstract public function getId();

    /**
     * Return a title in the users translation
     * @return string
     */
    abstract public function getTypeName();

    /**
     * @param iLubFieldDefinitionTypeOption $option
     * @return iLubFieldDefinitionTypeOption
     */
    abstract public function getValueDefinitionInputGUI(iLubFieldDefinitionTypeOption &$option);

    /**
     * @param iLubFieldDefinitionTypeOption $item
     * @param array                         $values
     */
    abstract public function setValues(iLubFieldDefinitionTypeOption $item, $values = array());

    /**
     * @param ilPropertyFormGUI $form
     * @return array
     */
    abstract public function getValues(ilPropertyFormGUI $form);

    /**
     * Define how this type is displayed in an ilFormPropertyGUI
     * @param string $title
     * @param string $postvar
     * @param array  $values
     * @return ilFormPropertyGUI
     */
    abstract public function getPresentationInputGUI($title, $postvar, $values);

    /**
     * @return string
     */
    public function __toString()
    {
        return 'type_id=' . $this->getId();
    }

    /**
     * @param int $type_id
     * @param \iLubFieldDefinitionType[]
     * @return bool|\iLubFieldDefinitionType
     */
    public static function getTypeByTypeId($type_id, $types)
    {
        foreach ($types as $type) {
            if ($type instanceof iLubFieldDefinitionType AND $type->getId() == $type_id) {
                return $type;
            }
        }

        return false;
    }
} 