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
 * Class iLubFieldDefinitionTypeOption
 *
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class iLubFieldDefinitionTypeOption extends ilRadioOption {

	/**
	 * @param string $info
	 */
	public function __construct($info = '') {
		parent::__construct('', '', $info);
	}


	/**
	 * @param bool $disabled
	 */
	public function setDisabled($disabled) {
		$this->disabled = $disabled;

		foreach($this->getSubItems() as $sub_item) {
			$this->disable($sub_item, $disabled);
		}
	}


	/**
	 * Disable items recursively
	 *
	 * @param object $item
	 * @param bool   $disabled
	 */
	protected function disable($item, $disabled) {
		if (method_exists($item , 'getSubItems')) {
			foreach ($item->getSubItems() as $sub_item) {
				$this->disable($sub_item, $disabled);
			}
		}

		if (method_exists($item, 'setDisabled')) {
			$item->setDisabled($disabled);
		}
	}



}