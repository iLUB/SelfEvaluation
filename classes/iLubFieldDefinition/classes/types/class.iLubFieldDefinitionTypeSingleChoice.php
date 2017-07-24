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
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */
class iLubFieldDefinitionTypeSingleChoice extends
		iLubFieldDefinitionTypeSelect {


	const TYPE_ID = 3;

	/**
	 * @return int
	 */
	public function getId() {
		return self::TYPE_ID;
	}


	/**
	 * Return a title in the users translation
	 *
	 * @return string
	 */
	public function getTypeName() {
		global $lng;
		$lng->loadLanguageModule('assessment');
		return $lng->txt('assSingleChoice');
	}


	/**
	 * @param string $title
	 * @param string $postvar
	 * @param array  $values
	 *
	 * @return ilFormPropertyGUI
	 */
	public function getPresentationInputGUI($title, $postvar, $values) {
		require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/InputGUIs/classes/class.ilRadioGroupInputGUIwrapper.php');
		$select = new ilRadioGroupInputGUIwrapper($title, $postvar);

		foreach($values as $key => $value)
		{

			$select->addOption(new ilRadioOption($value,$key));
		}

		return $select;
	}
}