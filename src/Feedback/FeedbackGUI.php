<?php
namespace ilub\plugin\SelfEvaluation\Feedback;

use ilSelfEvaluationQuestionBlockInterface;
use ilSelfEvaluationPlugin;
use ilToolbarGUI;
use ilTemplate;
use ilCtrl;
use ilObjSelfEvaluationGUI;
use ilGlobalTemplateInterface;
use ilAccess;
use ilSelfEvaluationVirtualOverallBlock;
use ilSelfEvaluationQuestionBlock;
use ilDBInterface;
use ilPropertyFormGUI;
use ilUtil;
use ilNonEditableValueGUI;
use ilTextInputGUI;
use ilConfirmationGUI;
use ilub\plugin\SelfEvaluation\UIHelper\TinyMceTextAreaInputGUI;
use ilRadioGroupInputGUI;
use ilRadioOption;
use ilub\plugin\SelfEvaluation\UIHelper\SliderInputGUI;

/**
 * @ilCtrl_Calls      ilSelfEvaluationFeedbackGUI:
 * @ilCtrl_IsCalledBy ilSelfEvaluationFeedbackGUI:
 */
class FeedbackGUI
{
    /**
     * @var ilPropertyFormGUI
     */
    protected $form;
    /**
     * @var ilTemplate
     */
    protected $overview;
    /**
     * @var int
     */
    protected $total;
    /**
     * @var ilSelfEvaluationQuestionBlockInterface
     */
    protected $block;
    /**
     * @var Feedback
     */
    protected $feedback;
    /**
     * @var ilDBInterface
     */
    protected $db;
    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilObjSelfEvaluationGUI
     */
    protected $parent;
    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;
    /**
     * @var ilAccess
     */
    protected $access;
    /**
     * @var ilSelfEvaluationPlugin
     */
    protected $plugin;

    function __construct(
        ilDBInterface $db,
        ilObjSelfEvaluationGUI $parent,
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ilCtrl,
        ilToolbarGUI $ilToolbar,
        ilAccess $access,
        ilSelfEvaluationPlugin $plugin
    ) {
        $this->db = $db;
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->parent = $parent;
        $this->toolbar = $ilToolbar;
        $this->access = $access;
        $this->plugin = $plugin;
    }

    public function executeCommand()
    {
        if ($_GET['parent_overall']) {
            $this->block = new ilSelfEvaluationVirtualOverallBlock($this->parent->object->getId(),$this->plugin);
        } else {
            $this->block = new ilSelfEvaluationQuestionBlock($_GET['block_id']);
        }

        if ($_GET['feedback_id']) {
            $this->feedback = new Feedback($_GET['feedback_id']);
        } else {
            $this->feedback = Feedback::_getNewInstanceByParentId($this->db,$this->getBlock()->getId());
        }
        if ($_GET['parent_overall']) {
            $this->feedback->setParentTypeOverall(true);
        }

        $this->performCommand();
    }

    /**
     * @return string
     */
    public function getStandardCommand()
    {
        return 'listObjects';
    }

    protected function performCommand()
    {
        $this->ctrl->saveParameter($this, 'block_id');
        $this->ctrl->saveParameter($this, 'parent_overall');
        $this->ctrl->saveParameter($this, 'feedback_id');
        $this->ctrl->saveParameterByClass('ilSelfEvaluationBlockGUI', 'block_id');

        $cmd = ($this->ctrl->getCmd()) ? $this->ctrl->getCmd() : $this->getStandardCommand();

        switch ($cmd) {
            case 'listObjects':
            case 'addNew':
            case 'cancel':
            case 'createObject':
            case 'updateObject':
            case 'editFeedback':
            case 'checkNextValue':
            case 'deleteFeedback':
            case 'deleteFeedbacks':
            case 'deleteObject':
                if (!$this->access->checkAccess("read", $cmd, $this->parent->object->getRefId(), $this->plugin->getId(),
                    $this->parent->object->getId())) {
                    throw new \ilObjectException($this->plugin->txt("permission_denied"));
                }
                $this->$cmd();
                break;
        }
    }

    protected function cancel()
    {
        $this->ctrl->setParameter($this, 'feedback_id', '');
        $this->ctrl->redirect($this);
    }

    protected function listObjects()
    {
        $this->toolbar->addButton('&lt;&lt; ' . $this->plugin->txt('back_to_blocks'),
            $this->ctrl->getLinkTargetByClass('ilSelfEvaluationListBlocksGUI', 'showContent'));
        $this->toolbar->addButton($this->plugin->txt('add_new_feedback'), $this->ctrl->getLinkTarget($this, 'addNew'));

        $ov = $this->getOverview();
        $table = new FeedbackTableGUI($this->db, $this,$this->plugin,'listObjects', $this->block);
        $this->tpl->setContent($ov->get() . '<br><br>' . $table->getHTML());
    }

    protected function addNew()
    {
        $this->initForm();
        $this->feedback->setStartValue(Feedback::_getNextMinValueForParentId($this->db,$this->block->getId(),
            $_GET['start_value'] ? $_GET['start_value'] : 0, 0, $this->feedback->isParentTypeOverall()));
        $this->feedback->setEndValue(Feedback::_getNextMaxValueForParentId($this->db,$this->block->getId(),
            $this->feedback->getStartValue(), 0, $this->feedback->isParentTypeOverall()));
        $this->setValues();
        $this->tpl->setContent($this->form->getHTML());
    }

    protected function checkNextValue()
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        $ignore = ($_GET['feedback_id'] ? $_GET['feedback_id'] : 0);
        $start = Feedback::_getNextMinValueForParentId($this->db,$this->block->getId(),
            $_GET['start_value'] ? $_GET['start_value'] : 0, $ignore, $this->feedback->isParentTypeOverall());
        $end = Feedback::_getNextMaxValueForParentId($this->db,$this->block->getID(), $start, $ignore,
            $this->feedback->isParentTypeOverall());

        $state = (($_GET['from'] < $start) OR ($_GET['to'] > $end)) ? false : true;
        echo json_encode([
            'check' => $state,
            'start_value' => $_GET['start_value'],
            'next_from' => $start,
            'next_to' => $end
        ]);
        exit;
    }

    protected function initForm($mode = 'create')
    {
        $this->form = new  ilPropertyFormGUI();
        $this->form->setTitle($this->plugin->txt($mode . '_feedback_form'));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        $this->form->addCommandButton($mode . 'Object', $this->plugin->txt($mode . '_feedback_button'));
        $this->form->addCommandButton('cancel', $this->plugin->txt('cancel'));
        // Block
        $te = new ilNonEditableValueGUI($this->plugin->txt('block'), 'block');
        $te->setValue($this->block->getTitle());
        $this->form->addItem($te);
        // Title
        $te = new ilTextInputGUI($this->plugin->txt('title'), 'title');
        $te->setRequired(true);
        $this->form->addItem($te);
        // Description
        $te = new ilTextInputGUI($this->plugin->txt('description'), 'description');
        $this->form->addItem($te);

        if ($mode == 'create') {
            $radio_options = new ilRadioGroupInputGUI($this->plugin->txt('feedback_range_type'), 'feedback_range_type');
            $option_auto = new ilRadioOption($this->plugin->txt("option_auto"), 'option_auto');
            $option_auto->setInfo($this->plugin->txt("option_auto_info"));

            $option_slider = new ilRadioOption($this->plugin->txt("option_slider"), 'option_slider');
            $sl = new SliderInputGUI($this->tpl, $this->plugin, $this->plugin->txt('slider'), 'slider', 0, 100,
                $this->ctrl->getLinkTarget($this, 'checkNextValue'));
            $option_slider->addSubItem($sl);

            if (Feedback::_isComplete($this->db,$this->block->getId(), $this->feedback->isParentTypeOverall())) {
                $option_slider->setDisabled(true);
            }

            $radio_options->addOption($option_auto);
            $radio_options->addOption($option_slider);

            $radio_options->setRequired(true);

            $this->form->addItem($radio_options);
        } else {
            $sl = new SliderInputGUI($this->tpl, $this->plugin,$this->plugin->txt('slider'), 'slider', 0, 100,
                $this->ctrl->getLinkTarget($this, 'checkNextValue'));
            $this->form->addItem($sl);
        }

        $te = new TinyMceTextAreaInputGUI($this->feedback->getId(), $this->plugin->txt('feedback_text'), 'feedback_text');
        $te->setRequired(true);
        $this->form->addItem($te);
    }

    protected function createObject()
    {
        $this->initForm();
        if ($this->form->checkInput()) {
            $obj = Feedback::_getNewInstanceByParentId($this->db,$this->block->getId(),
                $this->feedback->isParentTypeOverall());
            $obj->setTitle($this->form->getInput('title'));
            $obj->setDescription($this->form->getInput('description'));
            if ($this->form->getInput('feedback_range_type') == 'option_auto') {
                $range = Feedback::_rearangeFeedbackLinear($this->db,$this->block->getId(),
                    $this->feedback->isParentTypeOverall());
                $obj->setStartValue(100 - $range);
                $obj->setEndValue(100);
            } else {
                $slider = $this->form->getInput('slider');
                $obj->setStartValue($slider[0]);
                $obj->setEndValue($slider[1]);
            }

            $obj->setFeedbackText($this->form->getInput('feedback_text'));
            $obj->create();
            ilUtil::sendSuccess($this->plugin->txt('msg_feedback_created'));
            $this->cancel();
        }
        $this->form->setValuesByPost();
        $this->tpl->setContent($this->form->getHTML());
    }

    protected function setValues()
    {
        $values['title'] = $this->feedback->getTitle();
        $values['description'] = $this->feedback->getDescription();
        $values['start_value'] = $this->feedback->getStartValue();
        $values['end_value'] = $this->feedback->getEndValue();
        $values['feedback_text'] = $this->feedback->getFeedbackText();
        $values['slider'] = [$this->feedback->getStartValue(), $this->feedback->getEndValue()];
        if (Feedback::_isComplete($this->db,$this->block->getId(), $this->feedback->isParentTypeOverall())) {
            $values['feedback_range_type'] = 'option_auto';
        } else {
            $values['feedback_range_type'] = 'option_slider';
        }
        $this->form->setValuesByArray($values);
    }

    protected function editFeedback()
    {
        $this->initForm('update');
        $this->setValues();
        $this->tpl->setContent($this->form->getHTML());
    }

    protected function updateObject()
    {
        $this->initForm('update');
        if ($this->form->checkInput()) {
            $this->feedback->setTitle($this->form->getInput('title'));
            $this->feedback->setDescription($this->form->getInput('description'));
            $slider = $this->form->getInput('slider');
            $this->feedback->setStartValue($slider[0]);
            $this->feedback->setEndValue($slider[1]);
            $this->feedback->setFeedbackText($this->form->getInput('feedback_text'));
            $this->feedback->update();
            ilUtil::sendSuccess($this->plugin->txt('msg_feedback_created'));
            $this->cancel();
        }
        $this->form->setValuesByPost();
        $this->tpl->setContent($this->form->getHTML());
    }

    protected function deleteFeedback()
    {
        $this->deleteFeedbacksConfirmation([$this->feedback->getId()]);
    }

    protected function deleteFeedbacks()
    {
        $this->deleteFeedbacksConfirmation($_POST["id"]);
    }

    protected function deleteFeedbacksConfirmation($ids = [])
    {
        ilUtil::sendQuestion($this->plugin->txt('qst_delete_feedback'));
        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setCancel($this->plugin->txt('cancel'), 'cancel');
        $conf->setConfirm($this->plugin->txt('delete_feedback'), 'deleteObject');
        foreach ($ids as $id) {
            $obj = new Feedback($this->db, $id);
            $conf->addItem('id[]', $obj->getId(), $obj->getTitle());

        }
        $this->tpl->setContent($conf->getHTML());
    }

    protected function deleteObject()
    {
        ilUtil::sendSuccess($this->plugin->txt('msg_feedback_deleted'), true);

        $ids = $_POST["id"];
        foreach ($ids as $id) {
            $obj = new Feedback($this->db,$id);
            $obj->delete();
        }
        $this->cancel();
    }

    protected function getOverview() : ilTemplate
    {
        $this->overview= $this->plugin->getTemplate('default/Feedback/tpl.feedback_overview.html');

        $this->getMeasurement();
        $min = Feedback::_getNextMinValueForParentId($this->db,$this->block->getId(), 0, 0,
            $this->feedback->isParentTypeOverall());
        $feedbacks = Feedback::_getAllInstancesForParentId($this->db,$this->block->getId(), false,
            $this->feedback->isParentTypeOverall());

        if (count($feedbacks) == 0) {
            $this->parseOverviewBlock('blank', 100, 0);
            return $this->overview;
        }
        $fb = null;
        foreach ($feedbacks as $fb) {
            if ($min !== false AND $min <= $fb->getStartValue()) {
                $this->parseOverviewBlock('blank', $fb->getStartValue() - $min, $min);
            }
            $this->parseOverviewBlock('fb', $fb->getEndValue() - $fb->getStartValue(), $fb->getId(), $fb->getTitle());
            $min = Feedback::_getNextMinValueForParentId($this->db,$this->block->getId(), $fb->getEndValue(), 0,
                $this->feedback->isParentTypeOverall());
        }
        if ($min != 100 AND is_object($fb)) {
            $this->parseOverviewBlock('blank', 100 - $min, $min);
        }

        return $this->overview;
    }

    protected function getMeasurement()
    {
        for ($x = 1; $x <= 100; $x += 1) {
            $this->overview->setCurrentBlock('line');
            if ($x % 5 == 0) {
                $this->overview->setVariable('INT', $x . '&nbsp;');
                $this->overview->setVariable('LINE_CSS', '_double');
            } else {
                $this->overview->setVariable('INT', '');
            }
            $this->overview->setVariable('WIDTH', 10);
            $this->overview->parseCurrentBlock();
        }
    }

    public function parseOverviewBlock(string $type, int $width, int $value, string $title = '')
    {
        $href = '';
        $css = '';
        switch ($type) {
            case 'blank':
                $this->ctrl->setParameter($this, 'feedback_id', null);
                $this->ctrl->setParameter($this, 'start_value', $value);
                $href = ($this->ctrl->getLinkTarget($this, 'addNew'));
                $css = '_blank';
                $title = $this->plugin->txt('insert_feedback');
                break;
            case 'fb':
                $this->ctrl->setParameter($this, 'feedback_id', $value);
                $href = ($this->ctrl->getLinkTarget($this, 'editFeedback'));
                break;
        }
        $this->total += $width;
        $this->overview->setCurrentBlock('fb');
        $this->overview->setVariable('FEEDBACK', $title);
        $this->overview->setVariable('HREF', $href);
        $this->overview->setVariable('WIDTH', $width);
        $this->overview->setVariable('CSS', $css);
        $this->overview->parseCurrentBlock();
    }

    /**
     * @return ilSelfEvaluationQuestionBlockInterface
     */
    public function getBlock() : ilSelfEvaluationQuestionBlockInterface
    {
        return $this->block;
    }
}