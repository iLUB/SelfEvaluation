<?php
require_once(dirname(__FILE__) . '/../class.ilObjSelfEvaluationGUI.php');
require_once(dirname(__FILE__) . '/../Feedback/class.ilSelfEvaluationFeedbackGUI.php');
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('class.ilSelfEvaluationDatasetTableGUI.php');
require_once('class.ilSelfEvaluationCsvExport.php');

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
		$this->pl = $parent->getPluginObject();
		$this->dataset = new ilSelfEvaluationDataset($_GET['dataset_id'] ? $_GET['dataset_id'] : 0);
	}


	public function executeCommand() {
		if ($_GET['rl'] == 'true') {
			$this->pl->updateLanguages();
		}
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
		$this->tabs_gui->setTabActive('all_results');
		switch ($cmd) {
			default:
				$this->$cmd();
				break;
		}
	}


	public function selectResult() {
		$this->ctrl->setParameter($this, 'dataset_id', $_POST['select_result']);
		$this->ctrl->redirect($this, 'listMyObjects');
	}


	public function index() {
		$table = new ilSelfEvaluationDatasetTableGUI($this, 'index', $this->pl, $this->parent->object->getId());
		$this->tpl->setContent($table->getHTML());

		return;
	}

	public function show() {
		$content = $this->pl->getTemplate('default/Dataset/tpl.dataset_presentation.html');
        $content->setVariable('INTRO_HEADER', $this->parent->object->getOutroTitle());
		$content->setVariable('INTRO_BODY', $this->parent->object->getOutro());
		$feedback = '';
		if ($this->parent->object->getAllowShowResults()) {
			global $tpl;
			$tpl->addJavaScript('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/templates/js/bar_spider_chart_toggle.js');
			require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Feedback/class.ilSelfEvaluationFeedbackChartGUI.php');
			$charts = new ilSelfEvaluationFeedbackChartGUI();
			$feedback = $charts->getPresentationOfFeedback($this->dataset);
		}
		$this->tpl->setContent($content->get() . $feedback);
	}


	public function confirmDelete() {
		$conf = new ilConfirmationGUI();
        ilUtil::sendQuestion($this->pl->txt('qst_delete_dataset'));
        $conf->setFormAction($this->ctrl->getFormAction($this));
		$conf->setCancel($this->pl->txt('cancel'), 'index');
		$conf->setConfirm($this->pl->txt('delete_dataset'), 'delete');
		$conf->addItem('dataset_id', $this->dataset->getId(), date('d.m.Y - H:i:s', $this->dataset->getCreationDate()));
		$this->tpl->setContent($conf->getHTML());
	}


	public function delete() {
		ilUtil::sendSuccess($this->pl->txt('msg_dataset_deleted'), true);
		$this->dataset = new ilSelfEvaluationDataset($_POST['dataset_id']);
		$this->dataset->delete();
		$this->ctrl->redirect($this, 'index');
	}


	public function confirmDeleteAll() {
		$conf = new ilConfirmationGUI();
		$conf->setFormAction($this->ctrl->getFormAction($this));
		$conf->setCancel($this->pl->txt('cancel'), 'index');
		$conf->setConfirm($this->pl->txt('delete_all_datasets'), 'deleteAll');
		$conf->addItem('dataset_id', NULL, $this->pl->txt('confirm_delete_all_datasets'));
		$this->tpl->setContent($conf->getHTML());
	}


	public function deleteAll() {
		ilSelfEvaluationDataset::_deleteAllInstancesByObjectId(ilObject2::_lookupObjectId($_GET['ref_id']));
		ilUtil::sendSuccess($this->pl->txt('all_datasets_deleted'));
		$this->ctrl->redirect($this, 'index');
	}

    public function exportCsv() {
        $csvExport = new ilSelfEvaluationCsvExport($this->pl, ilObject2::_lookupObjectId($_GET['ref_id']));
        $csvExport->getCsvExport();
        exit;
    }
}

?>