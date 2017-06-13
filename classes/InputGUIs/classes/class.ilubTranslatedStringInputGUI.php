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
require_once('Customizing/global/plugins/Libraries/Translation/class.ilubTranslationUtil.php');

/**
 * @author Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class ilubTranslatedStringInputGUI extends ilSubEnabledFormPropertyGUI {

	const MODE_TEXT_AREA = 'area';
	const MODE_TEXT_INPUT = 'text';

	/**
	 * @var string
	 */
	protected $mode = self::MODE_TEXT_INPUT;
	/**
	 * @var bool
	 */
	protected $rich_type_editing = FALSE;
	/**
	 * @var array
	 */
	protected $language_ids;
	/**
	 * @var array
	 */
	protected $rte;
	/**
	 * @var bool
	 */
	protected $has_fields_initialised = FALSE;


	/**
	 * @param string $title this text is displayed to the user
	 * @param string $postvar name of the form input element
	 */
	public function __construct($title, $postvar) {
		parent::__construct($title, $postvar);
		$this->setMode(self::MODE_TEXT_INPUT);
		$this->setLanguageIds(ilubTranslationUtil::getAllLanguageIds());
	}


	public function initFields() {
		$this->setHasFieldsInitialised(TRUE);
		global $lng;
		$lng->loadLanguageModule('meta');

		if ($this->getMode() == self::MODE_TEXT_AREA) {
			$input_type = 'ilTextAreaInputGUI';
			/** @var ilTextAreaInputGUI $input */
		} else {
			$input_type = 'ilTextInputGUI';
			/** @var ilTextInputGUI $input */
		}

		// Create input fields
		require_once('Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php');
		$editing = new ilObjAdvancedEditing();
		foreach ($this->getLanguageIds() as $language) {
			$input = new $input_type($lng->txt('meta_l_' . $language), $this->getPostVar() . '_' . $language);
			// Set Rich Type Editing
			if ($this->hasRichTypeEditing() AND $this->getMode() == self::MODE_TEXT_AREA) {
				$input->setUseRte(TRUE);
				$input->setRTESupport($this->rte['obj_id'], $this->rte['obj_type'], $this->rte['module'],
					$this->rte['cfg_template'], $this->rte['hide_switch'], $this->rte['version']);
				$input->setRteTags($editing->_getUsedHTMLTags(''));
			}
			$this->sub_items[$language] = $input;
		}

		// Set required input fields
		if ($this->getRequired()) {
			$default_lng = $lng->getDefaultLanguage();
			if (count($this->getLanguageIds()) == 1) {
				// If there is only one language id set, mark it as required
				/** @var ilTextInputGUI|ilTextAreaInputGUI $item */
				$arr = $this->getLanguageIds();
				$item = $this->sub_items[$arr[0]];
				$item->setRequired(TRUE);
			} else if (array_key_exists($default_lng, $this->sub_items)) {
				// We only set the default language as required
				/** @var ilTextInputGUI|ilTextAreaInputGUI $item */
				$item = $this->sub_items[$default_lng];
				$item->setRequired(TRUE);
			}
		}
	}


	/**
	 * Check input, strip slashes etc. set alert, if input is not ok.
	 *
	 * @return	boolean		Input ok, true/false
	 */
	public function checkInput() {
		$sub_check =  $this->checkSubItemsInput();

		if ($sub_check) {
			$translations = array();
			foreach ($this->getLanguageIds() as $language) {
				$translations[$language] = $_POST[$this->getPostVar() . '_' . $language];
				unset($_POST[$this->getPostVar() . '_' . $language]);
			}
			$_POST[$this->getPostVar()] = $translations;
		}

		return $sub_check;
	}


	/**
	 * Insert property html
	 * This method is required by ilPropertyFormGUI, but somehow not inherited
	 *
	 * @param ilTemplate $a_tpl
	 */
	public function insert(&$a_tpl) {
		if (!$this->hasFieldsInitialised()) {
			$this->initFields();
		}
	}


	/**
	 * @param $values array ( postvar => array (language_key => value) )
	 */
	public function setValueByArray($values) {
		$this->initFields();

		foreach (ilubTranslationUtil::getAllLanguageIds() as $language) {
			/** @var ilTextInputGUI|ilTextAreaInputGUI $input */
			$input = $this->sub_items[$language];
			if ($input instanceof ilTextInputGUI OR $input instanceof ilTextAreaInputGUI) {
				if (array_key_exists($this->getPostVar(), $values) AND is_array($values[$this->getPostVar()])
						AND array_key_exists($language, $values[$this->getPostVar()])
				) {
						// Get Values from array (as in entry->translations)
						$input->setValue($values[$this->getPostVar()][$language]);
				} else if (isset($values[$this->getPostVar() . '_' . $language])) {
					// Get values from POST (due to ilTextInputGUI limitations arrays are not supported)
					$input->setValue($values[$this->getPostVar() . '_' . $language]);
				}
			}
		}
	}


	/**
	 * @param string $mode select MODE_TEXT_AREA to display a textarea or MODE_TEXT_INPUT for a text input gui.
	 */
	public function setMode($mode) {
		$this->mode = $mode;
	}


	/**
	 * @return string
	 */
	public function getMode() {
		return $this->mode;
	}


	/**
	 * @param boolean $rich_type_editing
	 */
	public function setRichTypeEditing($rich_type_editing) {
		$this->rich_type_editing = $rich_type_editing;
	}


	/**
	 * @return boolean
	 */
	public function hasRichTypeEditing() {
		return $this->rich_type_editing;
	}


	/**
	 * Activates RTE support (requires MODE_TEXT_AREA)
	 *
	 * The object id and object type are important settings that must be set correctly to allow image upload.
	 * When uploading images, TinyMCE requires access to the (uploaded) media object via its URL. This causes
	 * ilWebAccessChecker to check whether the user has read permissions on this object (by using the objects ref_id).
	 *
	 * A hack to use RTE in places without ref_ids, e.g. in UIHook plugins, is to set set the object id to '1' and the
	 * object type to 'tst'. Then ilWebAccessChecker only verifies that the user has read access to the repository.
	 *
	 * @param int    $obj_id    Object ID
	 * @param string $obj_type  Object Type
	 * @param string $module    ILIAS module
	 * @param        $cfg_template
	 * @param bool   $hide_switch
	 * @param string $version
	 */
	public function setRTESupport($obj_id, $obj_type, $module,
		$cfg_template = NULL, $hide_switch = FALSE, $version = '3.4.7') {
		$this->rte = array(
			'obj_id' => $obj_id,
			'obj_type' => $obj_type,
			'module' => $module, 
			'cfg_template' => $cfg_template,
			'hide_switch' => $hide_switch, 
			'version' => $version);
	}


	/**
	 * @param array $language_ids is an array of 2-letter language ids, e.g. array ('en', 'de')
	 *                            Input fields will be generated only for the supported language ids.
	 *                            By default, input fields are generated for all installed langauges.
	 */
	public function setLanguageIds($language_ids) {
		if (count($language_ids) == 0) {
			$this->language_ids = ilubTranslationUtil::getAllLanguageIds();
		} else {
			$this->language_ids = $language_ids;
		}
	}


	/**
	 * @return array
	 */
	public function getLanguageIds() {
		return $this->language_ids;
	}


	/**
	 * @param boolean $has_fields_initialised
	 */
	public function setHasFieldsInitialised($has_fields_initialised) {
		$this->has_fields_initialised = $has_fields_initialised;
	}


	/**
	 * @return boolean
	 */
	public function hasFieldsInitialised() {
		return $this->has_fields_initialised;
	}
}