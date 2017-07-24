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

require_once('Services/Form/classes/class.ilFormPropertyGUI.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/InputGUIs/classes/class.ilSelectColorInputOption.php');

/**
 * Class ilSelectColorInputGUI
 * Provides an input field where a color can be selected by choosing a colored square.
 *
 * @author Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class ilSelectColorInputGUI extends ilFormPropertyGUI {

	/**
	 * @var int
	 */
	protected $value;
	/**
	 * @var ilSelectColorInputOption[]
	 */
	protected $options = array();
	/**
	 * @var string
	 */
	protected $heading = ' ';

	/**
	 * Set Value.
	 *
	 * @param	string	$value	Value
	 */
	function setValue($value)
	{
		$this->value = $value;
	}


	/**
	 * Get Value.
	 *
	 * @return	string	Value
	 */
	function getValue()
	{
		return $this->value;
	}


	/**
	 * Set value by array
	 *
	 * @param	array	$a_values	value array
	 */
	function setValueByArray($a_values)
	{
		$this->setValue($a_values[$this->getPostVar()]);
	}


	/**
	 * @param ilSelectColorInputOption[]
	 */
	function setOptions($options) {
		$this->options = $options;
	}


	/**
	 * @return ilSelectColorInputOption[]
	 */
	function getOptions() {
		return $this->options;
	}


	/**
	 * @param string $info_txt
	 */
	public function setHeading($info_txt) {
		$this->heading = $info_txt;
	}


	/**
	 * @return string
	 */
	public function getHeading() {
		return $this->heading;
	}


	/**
	 * Check input, strip slashes etc. set alert, if input is not ok.
	 *
	 * @return	boolean		Input ok, true/false
	 */
	function checkInput()
	{
		global $lng;
		$_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
		$found = FALSE;
		foreach ($this->getOptions() as $options) {
			if ($_POST[$this->getPostVar()] == $options->getId()) {
				$found = TRUE;
			}
		}

		if ($found) {
			return TRUE;
		}

		if (!$this->getRequired() AND trim($_POST[$this->getPostVar()]) == '') {
			return TRUE;
		}

		if ($this->getRequired() AND !$found) {
			$this->setAlert($lng->txt('msg_input_is_required'));
		}

		return FALSE;
	}


	/**
	 * Render item
	 */
	function render($a_mode = '') {
		$color_tpl = new ilTemplate('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/InputGUIs/templates/tpl.select_color_input.html', TRUE, TRUE);
		$color_tpl->setVariable('TRANSPARENT_TITLE', $this->getHeading());

		$default_set = FALSE;
		foreach ($this->getOptions() as $option) {
			$color_tpl->setCurrentBlock('color');
			$color_tpl->setVariable('COLOR_CODE', $option->getColor());
			$color_tpl->setVariable('COLOR_TITLE', $option->getTitle());
			$color_tpl->setVariable('ID', $option->getId());
			if ($this->getValue() == $option->getId()) {
				// Set default color
				$color_tpl->setVariable('CLASS', 'selected');
				$color_tpl->setVariable('VALUE', $option->getId());
				$default_set = TRUE;

				if ($this->getDisabled()) {
					$color_tpl->setCurrentBlock('default_color');
					$color_tpl->setVariable('DEFAULT_CODE', $option->getColor());
					$color_tpl->parseCurrentBlock();
					$color_tpl->setCurrentBlock('default_title');
					$color_tpl->setVariable('DEFAULT_TITLE', $option->getTitle());
					$color_tpl->parseCurrentBlock();
					$color_tpl->setCurrentBlock('color');
				}
			}
			foreach ($option->getStyleProperties() as $property) {
				$color_tpl->setCurrentBlock('style_propperty');
				$color_tpl->setVariable('STYLE', $property);
			}
			$color_tpl->parseCurrentBlock();
		}

		// The customColorPicker needs a selected default color. Otherwise it throws exceptions on mouse out events.
		if (!$default_set) {
			$color_tpl->setVariable('TRANSPARENT_CLASS', 'selected');
		}

		$color_tpl->setVariable('POSTVAR', $this->getPostVar());
		return $color_tpl->get();
	}


	/**
	 * @param ilTemplate $a_tpl
	 *
	 * @return int|void
	 */
	function insert(&$a_tpl) {
		global $tpl;

		$tpl->addCss('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/InputGUIs/templates/custom_color_picker.css');
		$tpl->addCss('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/InputGUIs/templates/select_color_input.css');
		if (!$this->getDisabled()) {
			$tpl->addJavaScript('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/InputGUIs/js/custom_color_picker.js');
		}

		$html = $this->render();

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $html);
		$a_tpl->parseCurrentBlock();
	}
}