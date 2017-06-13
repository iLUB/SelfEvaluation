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
require_once('Services/JSON/classes/class.ilJsonUtil.php');

/**
 * Class ilFileDeleteInputGUI
 * This form input element displays a list of ilObjFiles along with a delete button.
 * The user can remove files from the list (through JavaScript).
 * The file Ids of the removed files are set as array in the post variable.
 *
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class ilFileDeleteInputGUI extends ilFormPropertyGUI {

	/**
	 * @var array stores the Ids of the files which should be deleted
	 */
	protected $values = array();
	/**
	 * @var ilObjFile[]
	 */
	protected $files = array();
	/**
	 * @var string
	 */
	protected $target_class;
	/**
	 * @var bool
	 */
	protected $is_download_allowed = TRUE;


	/**
	 * Constructor
	 *
	 * @param string    $a_title   Title
	 * @param string    $a_postvar Post Variable
	 * @param string    $target_class
	 */
	function __construct($a_title = '', $a_postvar = '', $target_class = '')
	{
		parent::__construct($a_title, $a_postvar);
		$this->setTargetClass($target_class);
	}


	/**
	 * @param string $target_class
	 */
	public function setTargetClass($target_class) {
		$this->target_class = $target_class;
	}


	/**
	 * @return string
	 */
	public function getTargetClass() {
		return $this->target_class;
	}


	/**
	 * @param array $values
	 */
	function setValues($values) {
		$this->values = $values;
	}


	/**
	 * @return array
	 */
	function getValues()	{
		return $this->values;
	}


	/**
	 * @param boolean $is_download_allowed
	 */
	public function setDownloadAllowed($is_download_allowed) {
		$this->is_download_allowed = $is_download_allowed;
	}


	/**
	 * @return boolean
	 */
	public function isDownloadAllowed() {
		return $this->is_download_allowed;
	}


	/**
	 * Set value by array
	 * @param array $a_values
	 */
	function setValueByArray($a_values) {
		if (count($a_values[$this->getPostVar()]) > 0 AND $a_values[$this->getPostVar()][0] instanceof ilObjFile) {
			$this->setFiles($a_values[$this->getPostVar()]);
		} else {
			// Set Ids of the files to delete (set by POST)
			$this->setValues($a_values[$this->getPostVar()]);
		}
	}


	/**
	 * @param ilObjFile[] $files
	 */
	function setFiles($files) {
		$this->files = $files;
	}


	/**
	 * Check input, strip slashes etc. set alert, if input is not ok.
	 *
	 * @return	boolean		Input ok, true/false
	 */
	function checkInput() {
		$_POST[$this->getPostVar()] = trim(ilUtil::stripSlashes($_POST[$this->getPostVar()]));
		$values = ilJsonUtil::decode($_POST[$this->getPostVar()]);
		if (is_array($values)) {
			$_POST[$this->getPostVar()] = array_unique($values);
			return TRUE;
		}
		if (is_null($values)) {
			$_POST[$this->getPostVar()] = array();
			return TRUE;
		}

		return FALSE;
	}


	/**
	 * Render item
	 */
	public function render($a_mode = '') {
		/** @var ilCtrl $ilCtrl */
		global $ilCtrl;
		$delete_tpl = new ilTemplate('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/InputGUIs/templates/tpl.delete_files.html', TRUE, TRUE);

		// make sure jQuery is loaded
		iljQueryUtil::initjQuery();

		foreach ($this->files as $file) {
			$delete_tpl->setCurrentBlock('file');

			// Download Links
			if ($this->getTargetClass() != '' AND $this->isDownloadAllowed()) {
				$ilCtrl->setParameterByClass($this->getTargetClass(), 'file_id', $file->getId());
				$delete_tpl->setCurrentBlock('icon');
				$delete_tpl->setVariable('LINK', $ilCtrl->getLinkTargetByClass($this->getTargetClass(), 'downloadFile'));
				$delete_tpl->setVariable('SRC_ICON', ilObject::_getIcon($file->getId()), 'small');
				$delete_tpl->setVariable('ALT_ICON', ilUtil::prepareFormOutput($file->getTitle()));
				$delete_tpl->setVariable('TITLE_ICON', ilUtil::prepareFormOutput($file->getTitle()));
				$delete_tpl->parseCurrentBlock();

				$delete_tpl->setCurrentBlock('file_link');
				$delete_tpl->setVariable('LINK', $ilCtrl->getLinkTargetByClass($this->getTargetClass(), 'downloadFile'));
				$delete_tpl->setVariable('TITLE', ilUtil::prepareFormOutput($file->getTitle()));
				$delete_tpl->parseCurrentBlock();
				$delete_tpl->setCurrentBlock('file');
			} else {
				$delete_tpl->setVariable('TITLE', ilUtil::prepareFormOutput($file->getTitle()));
			}

			$delete_tpl->setVariable('BUTTON_TEXT', 'X');

			// Items scheduled for deletion
			if (in_array($file->getId(), $this->getValues())) {
				$delete_tpl->setVariable('ROW_CLASS', ' ilFileDelete');
				$delete_tpl->setVariable('BUTTON_TEXT', '+');
			}

			$delete_tpl->setVariable('FILE_ID', ilUtil::prepareFormOutput($file->getId()));

			if ($this->getDisabled()) {
				$delete_tpl->setVariable('DISABLED', ' disabled="disabled"');
			}
			$delete_tpl->parseCurrentBlock();
		}
		$delete_tpl->setVariable('NAME', $this->getPostVar());
		$delete_tpl->setVariable('ID', $this->getFieldID());
		$json_values = ilJsonUtil::encode($this->getValues());
		$delete_tpl->setVariable('VALUE', ilUtil::prepareFormOutput($json_values));

		return $delete_tpl->get();
	}


	/**
	 * Insert property html
	 *
	 * @param ilTemplate $a_tpl
	 */
	function insert(&$a_tpl)
	{
		global $tpl;
		// needed styles
		$tpl->addCss(ilUtil::getStyleSheetLocation('filesystem', 'fileupload.css', 'Services/FileUpload'));
		$tpl->addCss('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/InputGUIs/templates/delete_files_input.css');
		$tpl->addJavaScript('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/InputGUIs/js/delete_files_input.js');
		$html = $this->render();

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $html);
		$a_tpl->parseCurrentBlock();
	}
}