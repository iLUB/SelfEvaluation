<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once(dirname(__FILE__).'/../class.ilObjSelfEvaluationGUI.php');
require_once('class.ilSelfEvaluationScale.php');
require_once(dirname(__FILE__).'/../Form/class.ilMultipleFieldInputGUI.php');
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


	/**
	 * @param      $parent_id
	 * @param bool $locked
	 */
	public function __construct($parent_id, $locked = false) {
		global $tpl, $ilCtrl;
		/**
		 * @var $tpl    ilTemplate
		 * @var $ilCtrl ilCtrl
		 */
		$this->tpl = $tpl;
		$this->locked = $locked;
		$this->ctrl = $ilCtrl;
		$this->pl = new ilSelfEvaluationPlugin();
		$this->parent_id = $parent_id;
		$this->obj = ilSelfEvaluationScale::_getInstanceByRefId($this->parent_id);
		$this->initForm();
	}


	protected function initForm() {
		// Header
		$te = new ilFormSectionHeaderGUI();
		$te->setTitle($this->pl->txt('scale_form'));
		$this->addItem($te);
		$te = new ilMultipleFieldInputGUI($this->pl->txt('scale'), 'scale', self::PLACEHOLDER);
		$te->setDisabled($this->locked);
		$this->addItem($te);
		// FillForm
		$this->fillForm();
	}


	/**
	 * @return array
	 */
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
	public function updateObject() {
		$this->obj->update();
		$units = array();
		if (is_array($_POST[self::PLACEHOLDER . '_new']['value'])) {
			foreach ($_POST[self::PLACEHOLDER . '_new']['value'] as $k => $v) {
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
		if (is_array($_POST[self::PLACEHOLDER . '_old']['value'])) {
			foreach ($_POST[self::PLACEHOLDER . '_old']['value'] as $k => $v) {
				if ($v) {
					$obj = new ilSelfEvaluationScaleUnit(str_replace('id_', '', $k));
					$obj->setTitle($_POST['scale_old']['title'][$k]);
					$obj->setValue($v);
					$obj->update();
					$units[] = $obj;
				} else {
					$obj = new ilSelfEvaluationScaleUnit(str_replace('id_', '', $k));
					$obj->delete();
				}
			}
		}
	}
}

?>