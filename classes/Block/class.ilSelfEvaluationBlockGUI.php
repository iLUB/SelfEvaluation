<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
require_once(dirname(__FILE__) . '/../Presentation/class.ilFormSectionHeaderGUIFixed.php');

/**
 * Class ilSelfEvaluationBlockGUI
 */
abstract class ilSelfEvaluationBlockGUI
{
    /**
     * @var ilPropertyFormGUI
     */
    protected $form;

    function __construct(
        int $parent_obj_id,
        int $parent_ref_id,
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ilCtrl,
        ilAccess $access,
        ilSelfEvaluationPlugin $plugin
    ) {
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->access = $access;
        $this->plugin = $plugin;

        $this->object = new ilSelfEvaluationQuestionBlock((int) $_GET['block_id']);
        $this->object->setParentId($parent_obj_id);

        $this->parent_ref_id = $parent_ref_id;
    }

    /**
     * @throws ilObjectException
     */
    public function executeCommand()
    {
        $this->ctrl->saveParameter($this, 'block_id');
        $this->performCommand();
    }

    /**
     * @return string
     */
    public function getStandardCommand()
    {
        return 'addBlock';
    }

    /**
     * @throws ilObjectException
     */
    protected function performCommand()
    {
        $cmd = ($this->ctrl->getCmd()) ? $this->ctrl->getCmd() : $this->getStandardCommand();

        switch ($cmd) {
            case 'addBlock':
            case 'createObject':
            case 'editBlock':
            case 'updateObject':
            case 'deleteBlock':
            case 'deleteObject':
            case 'duplicateBlock':
                if (!$this->checkAccess("write", $cmd)) {
                    throw new \ilObjectException($this->plugin->txt("permission_denied"));
                }
                $this->$cmd();
                break;
            case 'cancel':
                if (!$this->checkAccess("read", $cmd)) {
                    throw new \ilObjectException($this->plugin->txt("permission_denied"));
                }
                $this->$cmd();
                break;
        }
    }

    protected function checkAccess($permission, $cmd)
    {
        return $this->access->checkAccess($permission, $cmd, $this->parent_ref_id, $this->plugin->getId(),$this->object->getParentId());
    }

    /**
     * Show the add block input GUI
     */
    protected function addBlock()
    {
        $this->initForm();
        $this->tpl->setContent($this->form->getHTML());
    }

    protected function cancel()
    {
        $this->ctrl->redirectByClass('ilSelfEvaluationListBlocksGUI', 'showContent');
    }

    /**
     * Initialise the block form
     * @param string $mode create or update mode
     */
    protected function initForm($mode = 'create')
    {
        $this->form = new  ilPropertyFormGUI();
        $this->form->setTitle($this->plugin->txt($mode . '_block'));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        $this->form->addCommandButton($mode . 'Object', $this->plugin->txt($mode . '_block_button'));
        $this->form->addCommandButton('cancel', $this->plugin->txt('cancel'));

        $te = new ilTextInputGUI($this->plugin->txt('title'), 'title');
        $te->setRequired(true);
        $this->form->addItem($te);
        $te = new ilTextAreaInputGUI($this->plugin->txt('description'), 'description');
        $this->form->addItem($te);
    }

    /**
     * Create a new block object
     */
    protected function createObject()
    {
        $this->initForm();
        if ($this->form->checkInput()) {
            $this->setObjectValuesByPost();
            $this->object->create();
            ilUtil::sendSuccess($this->plugin->txt('msg_block_created'));
            $this->cancel();
        }
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * Create a new block object
     */
    protected function duplicateBlock()
    {
        $this->object->cloneTo($this->object->getParentId());
        ilUtil::sendSuccess($this->plugin->txt('msg_block_duplicated'), true);
        $this->cancel();
    }

    /**
     * Show the edit block GUI
     */
    protected function editBlock()
    {
        $this->initForm('update');
        $values = $this->getObjectValuesAsArray();
        $this->form->setValuesByArray($values);
        $this->tpl->setContent($this->form->getHTML());
    }

    protected function getObjectValuesAsArray()
    {
        $values['title'] = $this->object->getTitle();
        $values['description'] = $this->object->getDescription();

        return $values;
    }

    /**
     * Update a block object
     */
    protected function updateObject()
    {
        $this->initForm();
        $this->form->setValuesByPost();
        if ($this->form->checkInput()) {
            $this->setObjectValuesByPost();
            $this->object->update();
            ilUtil::sendSuccess($this->plugin->txt('msg_block_updated'));
            $this->cancel();
        }
        $this->tpl->setContent($this->form->getHTML());
    }

    protected function deleteBlock()
    {
        ilUtil::sendQuestion($this->plugin->txt('qst_delete_block'));
        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setCancel($this->plugin->txt('cancel'), 'cancel');
        $conf->setConfirm($this->plugin->txt('delete_block'), 'deleteObject');
        $conf->addItem('block_id', $this->object->getId(), $this->object->getTitle());
        $this->tpl->setContent($conf->getHTML());
    }

    protected function deleteObject()
    {
        ilUtil::sendSuccess($this->plugin->txt('msg_block_deleted'), true);
        $this->object->delete();
        $this->cancel();
    }

    protected function setObjectValuesByPost()
    {
        $this->object->setParentId($this->object->getParentId());
        $this->object->setTitle($this->form->getInput('title'));
        $this->object->setDescription($this->form->getInput('description'));
    }
}