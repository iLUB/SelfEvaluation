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
 * Class ilRadioGroupInputGUIwrapper
 * Improve input validation check: allowed POST values should match one of the set options.
 *
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 * @see https://www.ilias.de/mantis/view.php?id=13499
 */
class ilRadioGroupInputGUIwrapper extends ilRadioGroupInputGUI {
	/**
	 * Check input, strip slashes etc. set alert, if input is not ok.
	 *
	 * @return	boolean		Input ok, true/false
	 */
	function checkInput() {
		global $lng;

		$ok = parent::checkInput();
		// allow unset radio options
		if (!$this->getRequired() && trim($_POST[$this->getPostVar()]) == "")
		{
			return true;
		}

		$valid_option = FALSE;
		/** @var ilRadioOption $option */
		foreach ($this->getOptions() as $option) {
			// check that one of the set options is selected
			if ($_POST[$this->getPostVar()] == $option->getValue()) {
				$valid_option = TRUE;
			}
		}
		if (!$valid_option) {
			$this->setAlert($lng->txt("msg_input_does_not_match_regexp"));
		}

		return $ok AND $valid_option;
	}
}