<?php

include_once('./Services/Component/classes/class.ilPluginConfigGUI.php');
require_once('class.ilSelfEvaluationConfig.php');
require_once('class.ilSelfEvaluationPlugin.php');

/**
 * SelfEvaluation Configuration
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id$
 *
 */
class ilSelfEvaluationConfigGUI extends ilPluginConfigGUI {
	const TYPE_TEXT = 'ilTextInputGUI';
	const TYPE_CHECKBOX = 'ilCheckboxInputGUI';
	/**
	 * @var ilSelfEvaluationConfig
	 */
	protected $object;
	/**q
	 *
	 * @var array
	 */
	protected $fields = array();
	/**
	 * @var string
	 */
	protected $table_name = '';
	/**
	 * @var ilPropertyFormGUI
	 */
	protected $form;


	function __construct() {
		global $ilCtrl, $tpl, $ilTabs;
		/**
		 * @var $ilCtrl ilCtrl
		 * @var $tpl    ilTemplate
		 * @var $ilTabs ilTabsGUI
		 */
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		$this->tabs = $ilTabs;
		$this->pl = new ilSelfEvaluationPlugin();
		if($_GET['rl'] == 'true') {
			$this->pl->updateLanguages();
		}
		$this->object = new ilSelfEvaluationConfig($this->pl->getConfigTableName());
	}


	/**
	 * @return array
	 */
	public function getFields() {
		$this->fields = array(
			'async' => array(
				'type' => self::TYPE_CHECKBOX,
				'info' => false,
				'subelements' => NULL
			),
		);

		return $this->fields;
	}


	/**
	 * @return string
	 */
	public function getTableName() {
		return $this->table_name;
	}


	/**
	 * @return ilSelfEvaluationConfig
	 */
	public function getObject() {
		return $this->object;
	}


	/**
	 * @param $cmd
	 */
	function performCommand($cmd) {
		switch ($cmd) {
			case 'configure':
			case 'save':
			case 'svn':
				$this->$cmd();
				break;
		}
	}


	function configure() {
		$this->initConfigurationForm();
		$this->getValues();
		$this->tpl->setContent($this->form->getHTML());
	}


	public function getValues() {
		$values = array();
		foreach ($this->getFields() as $key => $item) {
			$values[$key] = $this->object->getValue($key);
			if (is_array($item['subelements'])) {
				foreach ($item['subelements'] as $subkey => $subitem) {
					$values[$key . '_' . $subkey] = $this->object->getValue($key . '_' . $subkey);
				}
			}
		}
		$this->form->setValuesByArray($values);
	}


	/**
	 * @return ilPropertyFormGUI
	 */
	public function initConfigurationForm() {
		global $lng, $ilCtrl;
		require_once('Services/Form/classes/class.ilPropertyFormGUI.php');
		$this->form = new ilPropertyFormGUI();
		foreach ($this->getFields() as $key => $item) {
			/** @var ilFormPropertyGUI $field */
			$field = new $item['type']($this->pl->txt($key), $key);
			if ($item['info']) {
				$field->setInfo($this->pl->txt($key . '_info'));
			}
			if (is_array($item['subelements'])) {
				/** @var ilSubEnabledFormPropertyGUI $field */
				foreach ($item['subelements'] as $subkey => $subitem) {
					$subfield = new $subitem['type']($this->pl->txt($key . '_' . $subkey), $key . '_' . $subkey);
					if ($subitem['info']) {
						/** @var ilFormPropertyGUI $subfield */
						$subfield->setInfo($this->pl->txt($key . '_info'));
					}
					$field->addSubItem($subfield);
				}
			}
			$this->form->addItem($field);
		}
		$this->form->addCommandButton('save', $lng->txt('save'));
		$this->form->setTitle($this->pl->txt('configuration'));
		$this->form->setFormAction($ilCtrl->getFormAction($this));

		return $this->form;
	}


	public function save() {
		global $tpl, $ilCtrl;
		$this->initConfigurationForm();
		if ($this->form->checkInput()) {
			foreach ($this->getFields() as $key => $item) {
				$this->object->setValue($key, $this->form->getInput($key));
				if (is_array($item['subelements'])) {
					foreach ($item['subelements'] as $subkey => $subitem) {
						$this->object->setValue($key . '_' . $subkey, $this->form->getInput($key . '_' . $subkey));
					}
				}
			}
			ilUtil::sendSuccess($this->pl->txt('conf_saved'));
			$ilCtrl->redirect($this, 'configure');
		} else {
			$this->form->setValuesByPost();
			$tpl->setContent($this->form->getHtml());
		}
	}
}

?>
