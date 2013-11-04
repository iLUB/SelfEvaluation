<?php
require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');
require_once('class.ilSelfEvaluationDataset.php');
//require_once(dirname(__FILE__) . '/../Question/class.ilSelfEvaluationQuestion.php');
//require_once(dirname(__FILE__) . '/../Feedback/class.ilSelfEvaluationFeedback.php');
require_once(dirname(__FILE__) . '/../Form/class.ilOverlayRequestGUI.php');
/**
 * TableGUI ilSelfEvaluationDatasetTableGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id:
 *
 */
class ilSelfEvaluationDatasetTableGUI extends ilTable2GUI {

	/**
	 * @param ilObjSelfEvaluationGUI $a_parent_obj
	 * @param string                 $a_parent_cmd
	 */
	function __construct(ilObjSelfEvaluationGUI $a_parent_obj, $a_parent_cmd) {
		global $ilCtrl, $ilTabs;
		/**
		 * @var $ilCtrl ilCtrl
		 * @var $ilTabs ilTabsGUI
		 */
		$this->pl = new ilSelfEvaluationPlugin();
		$this->ctrl = $ilCtrl;
		$this->tabs = $ilTabs;
		$this->setId('');
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setTitle($this->pl->txt('dataset_table_title'));
		//
		// Columns
		$this->addColumn($this->pl->txt('identity_type'), false, '100px');
		$this->addColumn($this->pl->txt('date'), false, 'auto');
		$this->addColumn($this->pl->txt('identity'), false, 'auto');
		$this->addColumn($this->pl->txt('percentage'), false, 'auto');
		$this->addColumn($this->pl->txt('actions'), false, 'auto');
		$this->ctrl->setParameterByClass('ilSelfEvaluationDatasetGUI', 'dataset_id', NULL);
		$this->setFormAction($ilCtrl->getFormActionByClass('ilSelfEvaluationDatasetGUI'));
		$this->setRowTemplate($this->pl->getDirectory() . '/templates/default/Dataset/tpl.template_dataset_row.html');
		switch ($a_parent_cmd) {
			case 'listObjects':
				$this->setData(ilSelfEvaluationDataset::_getAllInstancesByObjectId($a_parent_obj->object->getId(), true));
				break;
		}
	}


	/**
	 * @param array $a_set
	 */
	public function fillRow($a_set) {
		$obj = new ilSelfEvaluationDataset($a_set['id']);
		$identifier = new ilSelfEvaluationIdentity($obj->getIdentifierId());
		$this->ctrl->setParameterByClass('ilSelfEvaluationDatasetGUI', 'dataset_id', $obj->getId());
		// Row
		$this->tpl->setVariable('DATE', date('d.m.Y - H:i:s', $obj->getCreationDate()));
		switch ($identifier->getType()) {
			case ilSelfEvaluationIdentity::TYPE_EXTERNAL:
				$this->tpl->setVariable('TYPE', $this->pl->txt('identity_type_'
					. ilSelfEvaluationIdentity::TYPE_EXTERNAL));
				$this->tpl->setVariable('IDENTITY', $identifier->getIdentifier());
				break;
			case ilSelfEvaluationIdentity::TYPE_LOGIN:
				$this->tpl->setVariable('TYPE', $this->pl->txt('identity_type_'
					. ilSelfEvaluationIdentity::TYPE_LOGIN));
				$username = ilObjUser::_lookupName($identifier->getIdentifier());
				$this->tpl->setVariable('IDENTITY', $username['login']);
				break;
		}
		$this->tpl->setVariable('PERCENTAGE', $obj->getOverallPercentage());
		$this->tpl->setVariable('ID', $obj->getId());
		// Actions
		$ac = new ilAdvancedSelectionListGUI();
		$ac->setId('dataset_' . $obj->getId());
		$ac->addItem($this->pl->txt('delete_dataset'), 'delete_dataset', ilOverlayRequestGUI::getLink($this->ctrl->getLinkTargetByClass('ilSelfEvaluationDatasetGUI', 'confirmDelete')));
		$ac->addItem($this->pl->txt('show_feedback'), 'show_dataset', ilOverlayRequestGUI::getLink($this->ctrl->getLinkTargetByClass('ilSelfEvaluationDatasetGUI', 'show'), true));
		//		$ac->addItem($this->pl->txt('show_feedback'), 'show_dataset', $this->ctrl->getLinkTargetByClass('ilSelfEvaluationDatasetGUI', 'show'));
		$ac->setListTitle($this->pl->txt('actions'));
		//
		$this->ctrl->setParameterByClass('ilSelfEvaluationDatasetGUI', 'dataset_id', 0);
		$this->tpl->setVariable('ACTIONS', $ac->getHTML());
	}
}

?>