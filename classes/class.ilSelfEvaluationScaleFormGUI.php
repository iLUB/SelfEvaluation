<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('class.ilObjSelfEvaluationGUI.php');
require_once('class.ilSelfEvaluationScale.php');
require_once('class.ilMultipleFieldInputGUI.php');
require_once('./Services/Form/classes/class.ilHierarchyFormGUI.php');
/**
 * GUI-Class ilSelfEvaluationScaleGUI
 *
 * @author            Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @version           $Id:
 *
 * @ilCtrl_Calls      ilSelfEvaluationScaleGUI: ilObjSelfEvaluationGUI
 * @ilCtrl_IsCalledBy ilSelfEvaluationScaleGUI: ilCommonActionDispatcherGUI, ilObjSelfEvaluationGUI
 */
class ilSelfEvaluationScaleFormGUI extends ilPropertyFormGUI {
	const PLACEHOLDER = 'scale';
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs_gui;
	/**
	 * @var ilSelfEvaluationScale
	 */
	protected $obj;


	function __construct($parent_id = 0) {
		global $tpl, $ilCtrl;
		/**
		 * @var $tpl    ilTemplate
		 * @var $ilCtrl ilCtrl
		 */
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->tabs_gui = $this->parent->tabs_gui;
		$this->pl = new ilSelfEvaluationPlugin();
		$this->parent_id = $parent_id;
		$this->getObj();
		$this->initForm();
	}


	private function getObj() {
		$this->obj = ilSelfEvaluationScale::_getInstanceByRefId($this->parent_id);
	}


	protected function initForm() {
		// Header
		$te = new ilFormSectionHeaderGUI();
		$te->setTitle($this->pl->txt('scale_form'));
		$this->addItem($te);
		$te = new ilMultipleFieldInputGUI($this->pl->txt('scale'), 'scale', self::PLACEHOLDER);
		$this->addItem($te);
		// FillForm
		$this->fillForm();
	}


	public function fillForm() {
		foreach ($this->obj->units as $u) {
			/**
			 * @var $u ilSelfEvaluationScaleUnit
			 */
			$array[$u->getId()] = array( 'title' => $u->getTitle(), 'value' => $u->getValue() );
		}
		$array = array(
			'scale' => $array,
		);
		$this->setValuesByArray($array);

		return $array;
	}


	/**
	 * @param ilPropertyFormGUI $form_gui
	 *
	 * @return ilPropertyFormGUI
	 */
	public function appendToForm(ilPropertyFormGUI $form_gui) {
		foreach ($this->getItems() as $item) {
			$form_gui->addItem($item);
		}

		return $form_gui;
	}


	//
	// Create & Update Object
	//
	/*public function createObject() {
		$this->obj->create();
		$units = array();
		if (is_array($_POST['scale_new']['value'])) {
			foreach ($_POST['scale_new']['value'] as $k => $v) {
				if ($v) {
					$obj = new ilSelfEvaluationScaleUnit();
					$obj->setParentId($this->obj->getId());
					$obj->setTitle($_POST['scale_new']['title'][$k]);
					$obj->setValue($v);
					$obj->create();
					$units[] = $obj;
				}
			}
		}
		$this->getObj();
	}*/
	public function updateObject() {
		$this->obj->update();
		$units = array();
//		echo '<pre>' . print_r($_POST, 1) . '</pre>';
		if (is_array($_POST[self::PLACEHOLDER.'_new']['value'])) {
			foreach ($_POST[self::PLACEHOLDER.'_new']['value'] as $k => $v) {
				if ($v) {
					$obj = new ilSelfEvaluationScaleUnit();
					$obj->setParentId($this->obj->getId());
					$obj->setTitle($_POST['scale_new']['title'][$k]);
					$obj->setValue($v);
					$obj->create();
					$units[] = $obj;
				}
			}
		}
		if (is_array($_POST[self::PLACEHOLDER.'_old']['value'])) {
			foreach ($_POST[self::PLACEHOLDER.'_old']['value'] as $k => $v) {
				if ($v) {
					$obj = new ilSelfEvaluationScaleUnit(str_replace('id_', '', $k));
					$obj->setTitle($_POST['scale_old']['title'][$k]);
					$obj->setValue($v);
					$obj->update();
					$units[] = $obj;
				}
			}
		}
		$this->getObj();
	}
}

?>