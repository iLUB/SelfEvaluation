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
require_once('Services/Form/classes/class.ilTextInputGUI.php');

/**
 * Class ilCourseAutoCompleteInputGUI
 * A form input element to select course names through an asynchronous lookup.
 *
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class ilCourseAutoCompleteInputGUI extends ilTextInputGUI {

	/**
	 * @param string $title
	 * @param string $postvar
	 * @param string $class
	 * @param string $autocomplete_cmd
	 */
	public function __construct($title, $postvar, $class, $autocomplete_cmd) {
		/** @var ilCtrl $ilCtrl */
		global $ilCtrl;

		if (is_object($class)) {
			$class = get_class($class);
		}
		$class = strtolower($class);

		parent::__construct($title, $postvar);
		$this->setMaxLength(70);
		$this->setSize(30);
		$this->setDataSource($ilCtrl->getLinkTargetByClass($class, $autocomplete_cmd, '', TRUE));
	}


	/**
	 * Static asynchronous default auto complete function.
	 */
	static function echoAutoCompleteList() {
		$q = $_REQUEST['term'];
		require_once('Customizing/global/plugins/Libraries/AutoComplete/class.ilCourseAutoComplete.php');
		$search = new ilCourseAutoComplete();
		$list = $search->getList($q);
		echo $list;
	}


	/**
	 * @return	boolean		Input ok, true/false
	 */
	function checkInput() {
		global $lng;
		$valid = parent::checkInput();

		// Check for ilCourseAutoComplete Format
		if ($_POST[$this->getPostVar()] != '') {
			if (!preg_match('/^\[([0-9]+)\] (.+$)/', $_POST[$this->getPostVar()], $matches)) {
				$valid = FALSE;
				$this->setAlert($lng->txt('msg_input_does_not_match_regexp'));
			} else {
				$id = $matches[1];
				if (ilObject::_lookupType($id) != 'crs') {
					$valid = FALSE;
					$this->setAlert($lng->txt('msg_input_does_not_match_regexp'));
				}

				$_POST[$this->getPostVar()] = array();
				$_POST[$this->getPostVar()]['id'] = $id;
				$_POST[$this->getPostVar()]['title'] = $matches[2];
			}
		}

		return $valid;
	}


	/**
	 * Override parent to handle array parameters.
	 * @param string $value
	 */
	function setValue($value) {
		if (is_array($value)) {
			// Check for proper format
			if (array_key_exists('id', $value) AND array_key_exists('title', $value)) {
				$this->value = '[' . $value['id'] . ']' . ' ' . $value['title'];
			} else {
				$this->value = implode(', ', $value);
			}
		} else {
			$this->value = $value;
		}
	}
}