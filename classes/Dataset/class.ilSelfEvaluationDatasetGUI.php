<?php
require_once(dirname(__FILE__) . '/../class.ilObjSelfEvaluationGUI.php');
require_once(dirname(__FILE__) . '/../Feedback/class.ilSelfEvaluationFeedbackGUI.php');
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
/**
 * GUI-Class ilSelfEvaluationResultsGUI
 *
 * @author            Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @version           $Id:
 *
 * @ilCtrl_Calls      ilSelfEvaluationResultsGUI:
 * @ilCtrl_IsCalledBy ilSelfEvaluationResultsGUI:
 */
class ilSelfEvaluationDatasetGUI {

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs_gui;


	function __construct(ilObjSelfEvaluationGUI $parent) {
		global $tpl, $ilCtrl, $ilToolbar;
		/**
		 * @var $tpl       ilTemplate
		 * @var $ilCtrl    ilCtrl
		 * @var $ilToolbar ilToolbarGUI
		 */
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->parent = $parent;
		$this->toolbar = $ilToolbar;
		$this->tabs_gui = $this->parent->tabs_gui;
		$this->pl = new ilSelfEvaluationPlugin();
		$this->dataset = new ilSelfEvaluationDataset($_GET['dataset_id'] ? $_GET['dataset_id'] : 0);
	}


	public function executeCommand() {
		$cmd = ($this->ctrl->getCmd()) ? $this->ctrl->getCmd() : $this->getStandardCommand();
		switch ($cmd) {
			default:
				$this->performCommand($cmd);
				break;
		}

		return true;
	}


	/**
	 * @return string
	 */
	public function getStandardCommand() {
		return 'show';
	}


	/**
	 * @param $cmd
	 */
	function performCommand($cmd) {
		switch ($cmd) {
			default:
				$this->$cmd();
				break;
		}
	}


	public function selectResult() {
		$this->ctrl->setParameter($this, 'dataset_id', $_POST['select_result']);
		$this->ctrl->redirect($this, 'listObjects');
	}


	public function listObjects() {
		$this->tabs_gui->setTabActive('results');
		$se = new ilSelectInputGUI($this->pl->txt('select_result'), 'select_result');
		foreach (ilSelfEvaluationDataset::_getAllInstancesByIdentifierId($_GET['uid']) as $ds) {
			$opt[$ds->getId()] = $this->pl->txt('dataset_from') . ' ' . date('d.m.Y - H:i:s', $ds->getCreationDate());
		}
		$se->setOptions($opt);
		$this->toolbar->setFormAction($this->ctrl->getFormAction($this));
		$this->toolbar->addInputItem($se);
		$this->toolbar->addFormButton($this->pl->txt('select_result_button'), 'selectResult');
		if ($this->parent->object->getAllowShowResults() AND $this->dataset->getId() != 0) {
			$feedback = ilSelfEvaluationFeedbackGUI::_getPresentationOfFeedback($this->dataset, $this->parent->object->getShowCharts());
			$header = '<h1>' . $this->pl->txt('dataset_from') . ' '
				. date('d.m.Y - H:i:s', $this->dataset->getCreationDate()) . '</h1>';
			$this->tpl->setContent($header . $feedback);
		}
		if (! $this->parent->object->getAllowShowResults()) {
			ilUtil::sendFailure($this->pl->txt('msg_not_allowd_view_results'), true);
			$this->ctrl->redirect($this->parent);
		}
	}


	public function show() {
		$this->tabs_gui->setTabActive('content');
		$content = $this->pl->getTemplate('default/tpl.content.html');
		$content->setVariable('INTRO_HEADER', $this->pl->txt('outro_header'));
		$content->setVariable('INTRO_BODY', $this->parent->object->getIntro());
		if ($this->parent->object->getAllowShowResults()) {
			$feedback = ilSelfEvaluationFeedbackGUI::_getPresentationOfFeedback($this->dataset, $this->parent->object->getShowCharts());
		}
		$this->tpl->setContent($content->get() . $feedback);
	}
}

?>