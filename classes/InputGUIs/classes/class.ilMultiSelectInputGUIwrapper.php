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
 * Class ilMultiSelectInputGUIwrapper
 * Improve input validation check: allowed POST values should match one of the set options.
 * Make width and height setting configurable.
 *
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 * @see https://www.ilias.de/mantis/view.php?id=13499
 */
class ilMultiSelectInputGUIwrapper extends ilMultiSelectInputGUI {
	/**
	 * Check input, strip slashes etc. set alert, if input is not ok.
	 *
	 * @return	boolean		Input ok, true/false
	 */
	function checkInput() {
		global $lng;

		$ok = parent::checkInput();
		if ($ok) {
			foreach ($_POST[$this->getPostVar()] as $k => $v) {
				// Ignore 'select all'
				if ($this->select_all AND $k == 0 AND $_POST[$this->getPostVar()][$k] == '') {
					continue;
				}
				// Alert on invalid option
				if (!array_key_exists($_POST[$this->getPostVar()][$k], $this->getOptions()))
				{
					$this->setAlert($lng->txt("msg_input_does_not_match_regexp"));
					return FALSE;
				}
			}
		}

		return $ok;
	}
}