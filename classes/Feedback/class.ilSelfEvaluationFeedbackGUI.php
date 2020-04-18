<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once(dirname(__FILE__) . '/../class.ilObjSelfEvaluationGUI.php');
require_once(dirname(__FILE__) . '/../Block/class.ilSelfEvaluationVirtualOverallBlock.php');
require_once('class.ilSelfEvaluationFeedback.php');
require_once('class.ilSelfEvaluationFeedbackTableGUI.php');
require_once('class.ilSliderInputGUI.php');
require_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');

/**
 * GUI-Class ilSelfEvaluationFeedbackGUI
 * @author            Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @author            Fabio Heer
 * @version           $Id:
 * @ilCtrl_Calls      ilSelfEvaluationFeedbackGUI:
 * @ilCtrl_IsCalledBy ilSelfEvaluationFeedbackGUI:
 */
class ilSelfEvaluationFeedbackGUI
{
    /**
     * @var ilPropertyFormGUI
     */
    protected $form;
    /**
     * @var ilGlobalPageTemplate
     */
    protected $ov;
    /**
     * @var int
     */
    protected $total;

    /**
     * @var ilSelfEvaluationQuestionBlockInterface
     */
    protected $block;

    /**
     * @var ilSelfEvaluationFeedback
     */
    protected $object;

    /**
     * @var array
     */
    function __construct(
        ilObjSelfEvaluationGUI $parent,
        ilGlobalPageTemplate $tpl,
        ilCtrl $ilCtrl,
        ilToolbarGUI $ilToolbar,
        ilAccess $access,
        ilSelfEvaluationPlugin $plugin
    ) {
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
            $this->object = new ilSelfEvaluationFeedback($_GET['feedback_id']);
        } else {
            $this->object = ilSelfEvaluationFeedback::_getNewInstanceByParentId($this->getBlock()->getId());
        }
        if ($_GET['parent_overall']) {
            $this->object->setParentTypeOverall(true);
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
        $table = new ilSelfEvaluationFeedbackTableGUI($this, 'listObjects', $this->block);
        $this->tpl->setContent($ov->get() . '<br><br>' . $table->getHTML());
    }

    protected function addNew()
    {
        $this->initForm();
        $this->object->setStartValue(ilSelfEvaluationFeedback::_getNextMinValueForParentId($this->block->getId(),
            $_GET['start_value'] ? $_GET['start_value'] : 0), 0, $this->object->isParentTypeOverall());
        $this->object->setEndValue(ilSelfEvaluationFeedback::_getNextMaxValueForParentId($this->block->getId(),
            $this->object->getStartValue()), 0, $this->object->isParentTypeOverall());
        $this->setValues();
        $this->tpl->setContent($this->form->getHTML());
    }

    protected function checkNextValue()
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        $ignore = ($_GET['feedback_id'] ? $_GET['feedback_id'] : 0);
        $start = ilSelfEvaluationFeedback::_getNextMinValueForParentId($this->block->getId(),
            $_GET['start_value'] ? $_GET['start_value'] : 0, $ignore, $this->object->isParentTypeOverall());
        $end = ilSelfEvaluationFeedback::_getNextMaxValueForParentId($this->block->getID(), $start, $ignore,
            $this->object->isParentTypeOverall());

        $state = (($_GET['from'] < $start) OR ($_GET['to'] > $end)) ? false : true;
        echo json_encode(array(
            'check' => $state,
            'start_value' => $_GET['start_value'],
            'next_from' => $start,
            'next_to' => $end
        ));
        //$this->tpl->hide = true; // TODO this looks very nasty (writing to the global template object)
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
            $sl = new ilSliderInputGUI($this->tpl, $this->plugin->txt('slider'), 'slider', 0, 100,
                $this->ctrl->getLinkTarget($this, 'checkNextValue'));
            $option_slider->addSubItem($sl);

            if (ilSelfEvaluationFeedback::_isComplete($this->block->getId(), $this->object->isParentTypeOverall())) {
                $option_slider->setDisabled(true);
            }

            $radio_options->addOption($option_auto);
            $radio_options->addOption($option_slider);

            $radio_options->setRequired(true);

            $this->form->addItem($radio_options);
        } else {
            $sl = new ilSliderInputGUI($this->tpl, $this->plugin->txt('slider'), 'slider', 0, 100,
                $this->ctrl->getLinkTarget($this, 'checkNextValue'));
            $this->form->addItem($sl);
        }

        // Feedbacktext
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Form/class.ilTinyMceTextAreaInputGUI.php');
        $te = new ilTinyMceTextAreaInputGUI($this->parent->object, $this->plugin->txt('feedback_text'), 'feedback_text');
        $te->setRequired(true);
        $this->form->addItem($te);
    }

    protected function createObject()
    {
        $this->initForm();
        if ($this->form->checkInput()) {
            $obj = ilSelfEvaluationFeedback::_getNewInstanceByParentId($this->block->getId(),
                $this->object->isParentTypeOverall());
            $obj->setTitle($this->form->getInput('title'));
            $obj->setDescription($this->form->getInput('description'));
            if ($this->form->getInput('feedback_range_type') == 'option_auto') {
                $range = ilSelfEvaluationFeedback::_rearangeFeedbackLinear($this->block->getId(),
                    $this->object->isParentTypeOverall());
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
        $values['title'] = $this->object->getTitle();
        $values['description'] = $this->object->getDescription();
        $values['start_value'] = $this->object->getStartValue();
        $values['end_value'] = $this->object->getEndValue();
        $values['feedback_text'] = $this->object->getFeedbackText();
        $values['slider'] = array($this->object->getStartValue(), $this->object->getEndValue());
        if (ilSelfEvaluationFeedback::_isComplete($this->block->getId(), $this->object->isParentTypeOverall())) {
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
            $this->object->setTitle($this->form->getInput('title'));
            $this->object->setDescription($this->form->getInput('description'));
            $slider = $this->form->getInput('slider');
            $this->object->setStartValue($slider[0]);
            $this->object->setEndValue($slider[1]);
            $this->object->setFeedbackText($this->form->getInput('feedback_text'));
            $this->object->update();
            ilUtil::sendSuccess($this->plugin->txt('msg_feedback_created'));
            $this->cancel();
        }
        $this->form->setValuesByPost();
        $this->tpl->setContent($this->form->getHTML());
    }

    protected function deleteFeedback()
    {
        $this->deleteFeedbacksConfirmation([$this->object->getId()]);
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
            $obj = new ilSelfEvaluationFeedback($id);
            $conf->addItem('id[]', $obj->getId(), $obj->getTitle());

        }
        $this->tpl->setContent($conf->getHTML());
    }

    protected function deleteObject()
    {
        ilUtil::sendSuccess($this->plugin->txt('msg_feedback_deleted'), true);

        $ids = $_POST["id"];
        foreach ($ids as $id) {
            $obj = new ilSelfEvaluationFeedback($id);
            $obj->delete();
        }
        $this->cancel();
    }

    protected function getOverview() : ilTemplate
    {
        $this->getMeasurement();
        $min = ilSelfEvaluationFeedback::_getNextMinValueForParentId($this->block->getId(), 0, 0,
            $this->object->isParentTypeOverall());
        $feedbacks = ilSelfEvaluationFeedback::_getAllInstancesForParentId($this->block->getId(), false,
            $this->object->isParentTypeOverall());

        if (count($feedbacks) == 0) {
            $this->parseOverviewBlock('blank', 100, 0);
            return $this->ov;
        }
        $fb = null;
        foreach ($feedbacks as $fb) {
            if ($min !== false AND $min <= $fb->getStartValue()) {
                $this->parseOverviewBlock('blank', $fb->getStartValue() - $min, $min);
            }
            $this->parseOverviewBlock('fb', $fb->getEndValue() - $fb->getStartValue(), $fb->getId(), $fb->getTitle());
            $min = ilSelfEvaluationFeedback::_getNextMinValueForParentId($this->block->getId(), $fb->getEndValue(), 0,
                $this->object->isParentTypeOverall());
        }
        if ($min != 100 AND is_object($fb)) {
            $this->parseOverviewBlock('blank', 100 - $min, $min);
        }

        return $this->ov;
    }

    public function getMeasurement()
    {
        $this->ov = $this->plugin->getTemplate('default/Feedback/tpl.feedback_overview.html');
        for ($x = 1; $x <= 100; $x += 1) {
            $this->ov->setCurrentBlock('line');
            if ($x % 5 == 0) {
                $this->ov->setVariable('INT', $x . '&nbsp;');
                $this->ov->setVariable('LINE_CSS', '_double');
            } else {
                $this->ov->setVariable('INT', '');
            }
            $this->ov->setVariable('WIDTH', 10);
            $this->ov->parseCurrentBlock();
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
        $this->ov->setCurrentBlock('fb');
        $this->ov->setVariable('FEEDBACK', $title);
        $this->ov->setVariable('HREF', $href);
        $this->ov->setVariable('WIDTH', $width);
        $this->ov->setVariable('CSS', $css);
        $this->ov->parseCurrentBlock();
    }

    /**
     * @return ilSelfEvaluationQuestionBlockInterface
     */
    public function getBlock() : ilSelfEvaluationQuestionBlockInterface
    {
        return $this->block;
    }
}