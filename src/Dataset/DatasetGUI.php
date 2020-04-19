<?php
namespace ilub\plugin\SelfEvaluation\Dataset;

/**
 * @ilCtrl_Calls      ilSelfEvaluationResultsGUI:
 * @ilCtrl_IsCalledBy ilSelfEvaluationResultsGUI:
 */
class DatasetGUI
{
    function __construct(
        ilObjSelfEvaluationGUI $parent,
        ilGlobalPageTemplate $tpl,
        ilCtrl $ilCtrl,
        ilToolbarGUI $ilToolbar,
        ilAccess $access,
        ilSelfEvaluationPlugin $plugin,
        string $identifier = ""
    ) {
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->parent = $parent;
        $this->toolbar = $ilToolbar;
        $this->plugin = $plugin;
        $this->access = $access;
        $this->identifier = $identifier;
    }

    public function executeCommand()
    {
        $this->performCommand();
    }

    /**
     * @return string
     */
    public function getStandardCommand()
    {
        return 'show';
    }

    /**
     * @param $cmd
     */
    function performCommand()
    {
        $cmd = ($this->ctrl->getCmd()) ? $this->ctrl->getCmd() : $this->getStandardCommand();

        switch ($cmd) {
            default:
                $this->$cmd();
                break;
        }
    }

    public function selectResult()
    {
        $this->ctrl->setParameter($this, 'dataset_id', $_POST['select_result']);
        $this->ctrl->redirect($this, 'listMyObjects');
    }

    public function index()
    {
        if ($this->access->checkAccess("write", "index", $this->parent->object->getRefId(), $this->plugin->getId())) {
            $this->toolbar->addButton($this->plugin->txt('delete_all_datasets'),
                $this->ctrl->getLinkTargetByClass('ilSelfEvaluationDatasetGUI', 'confirmDeleteAll'));
            $this->toolbar->addButton($this->plugin->txt('export_csv'),
                $this->ctrl->getLinkTargetByClass('ilSelfEvaluationDatasetGUI', 'exportCSV'));
            $table = new ilSelfEvaluationDatasetTableGUI($this, 'index', $this->plugin, $this->parent->object->getId());
        } else {
            $table = new ilSelfEvaluationDatasetTableGUI($this, 'index', $this->plugin, $this->parent->object->getId(),$this->identifier);
        }

        $this->tpl->setContent($table->getHTML());
    }

    public function show()
    {
        $content = $this->plugin->getTemplate('default/Dataset/tpl.dataset_presentation.html');
        $content->setVariable('INTRO_HEADER', $this->parent->object->getOutroTitle());
        $content->setVariable('INTRO_BODY', $this->parent->object->getOutro());
        $feedback = '';
        if ($this->parent->object->getAllowShowResults()) {
            $this->tpl->addJavaScript('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/templates/js/bar_spider_chart_toggle.js');
            require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Feedback/class.ilSelfEvaluationFeedbackChartGUI.php');
            $charts = new ilSelfEvaluationFeedbackChartGUI($this->tpl);
            $this->dataset = new ilSelfEvaluationDataset($_GET['dataset_id'] ? $_GET['dataset_id'] : 0);
            $feedback = $charts->getPresentationOfFeedback($this->dataset);
        }
        require_once('Services/PDFGeneration/classes/factory/class.ilHtmlToPdfTransformerFactory.php');

        $this->tpl->setContent($content->get() . $feedback);
    }

    public function deleteDataset()
    {
        $this->confirmDelete([$this->dataset->getId()]);
    }

    public function deleteDatasets()
    {
        $this->confirmDelete($_POST["id"]);
    }

    /**
     * @param array $ids
     */
    public function confirmDelete($ids = [])
    {
        $conf = new ilConfirmationGUI();
        ilUtil::sendQuestion($this->plugin->txt('qst_delete_dataset'));
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setCancel($this->plugin->txt('cancel'), 'index');
        $conf->setConfirm($this->plugin->txt('delete_dataset'), 'delete');
        foreach ($ids as $id) {
            $dataset = new ilSelfEvaluationDataset($id);
            $identifier = new ilSelfEvaluationIdentity($dataset->getIdentifierId());
            $user = $identifier->getIdentifier();
            if ($identifier->getType() == $identifier::TYPE_LOGIN) {
                $user = (new ilObjUser($identifier->getIdentifier()))->getPublicName();
            }
            $conf->addItem('dataset_ids[]', $id, $user . " " . date('d.m.Y - H:i:s', $dataset->getCreationDate()));
        }
        $this->tpl->setContent($conf->getHTML());
    }

    public function delete()
    {
        ilUtil::sendSuccess($this->plugin->txt('msg_dataset_deleted'), true);
        foreach ($_POST['dataset_ids'] as $id) {
            $this->dataset = new ilSelfEvaluationDataset($id);
            $this->dataset->delete();
        }

        $this->ctrl->redirect($this, 'index');
    }

    public function confirmDeleteAll()
    {
        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setCancel($this->plugin->txt('cancel'), 'index');
        $conf->setConfirm($this->plugin->txt('delete_all_datasets'), 'deleteAll');
        $conf->addItem('dataset_id', null, $this->plugin->txt('confirm_delete_all_datasets'));
        $this->tpl->setContent($conf->getHTML());
    }

    public function deleteAll()
    {
        ilSelfEvaluationDataset::_deleteAllInstancesByObjectId(ilObject2::_lookupObjectId($_GET['ref_id']));
        ilUtil::sendSuccess($this->plugin->txt('all_datasets_deleted'));
        $this->ctrl->redirect($this, 'index');
    }

    public function exportCsv()
    {
        $csvExport = new ilSelfEvaluationCsvExport($this->plugin, ilObject2::_lookupObjectId($_GET['ref_id']));
        $csvExport->getCsvExport();
        exit;
    }
}

