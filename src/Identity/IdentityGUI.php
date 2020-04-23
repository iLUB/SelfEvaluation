<?php

namespace ilub\plugin\SelfEvaluation\Identity;

use ilAccordionGUI;
use ilPropertyFormGUI;
use ilObjSelfEvaluationGUI;
use ilGlobalPageTemplate;
use ilSelfEvaluationPlugin;
use ilCtrl;
use ilTextInputGUI;
use ilNonEditableValueGUI;
use ilUtil;
use ilDBInterface;

class IdentityGUI
{
    /**
     * @var ilPropertyFormGUI
     */
    protected $ex;
    /**
     * @var ilPropertyFormGUI
     */
    protected $new;

    /**
     * @var ilSelfEvaluationPlugin
     */
    protected $plugin;

    /**
     * @var ilGlobalPageTemplate
     */
    protected $tpl;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @var ilObjSelfEvaluationGUI
     */
    protected $parent;

    function __construct(
        ilObjSelfEvaluationGUI $parent,
        ilGlobalPageTemplate $tpl,
        ilCtrl $ilCtrl,
        ilSelfEvaluationPlugin $plugin,
        ilDBInterface $db
    ) {
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->parent = $parent;
        $this->plugin = $plugin;
        $this->db = $db;
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

    function performCommand()
    {
        if (!$this->parent->object->isIdentitySelection()) {
            $this->startWithNewUid();
        }

        $cmd = ($this->ctrl->getCmd()) ? $this->ctrl->getCmd() : $this->getStandardCommand();

        switch ($cmd) {
            case 'show':
            case 'addNew':
            case 'cancel':
            case 'startWithExistingUid':
            case 'startWithNewUid':
                $this->$cmd();
                break;
        }
    }

    public function show()
    {
        $this->initExistingForm();
        $this->initNewForm();
        $this->plugin->updateLanguages();
        $template = $this->plugin->getTemplate('default/Identity/tpl.identity_selection.html');
        $template->setVariable('IDENTITY_INFO_TEXT', $this->parent->object->getIdentitySelectionInfoText());
        $acc = new ilAccordionGUI();
        $acc->setOrientation(ilAccordionGUI::VERTICAL);
        $acc->addItem($this->plugin->txt('start_with_new_identity'), $this->new->getHTML());
        $acc->addItem($this->plugin->txt('start_with_existing_identity'), $this->ex->getHTML());
        $template->setVariable('IDENTITY_SELECTION', $acc->getHTML());
        $this->tpl->setContent($template->get());
    }

    public function initExistingForm()
    {
        $this->ex = new ilPropertyFormGUI();
        $this->ex->setFormAction($this->ctrl->getFormAction($this));
        $te = new ilTextInputGUI($this->plugin->txt('uid'), 'uid');
        $te->setRequired(true);
        $this->ex->addItem($te);
        $this->ex->addCommandButton('startWithExistingUid', $this->plugin->txt('start'));
    }

    public function initNewForm()
    {
        $this->new = new ilPropertyFormGUI();
        $this->new->setFormAction($this->ctrl->getFormAction($this));
        $te = new ilNonEditableValueGUI($this->plugin->txt('new_uid'), 'uid');
        $te->setRequired(true);
        $this->new->addItem($te);
        $this->new->addCommandButton('startWithNewUid', $this->plugin->txt('start'));
    }

    public function startWithExistingUid()
    {
        $this->initExistingForm();
        if ($this->ex->checkInput()) {
            $identifier = $this->ex->getInput('uid');
            if (Identity::_identityExists($this->db, $this->parent->object->getId(), $identifier)) {
                $id = Identity::_getInstanceForObjIdAndIdentifier($this->db, $this->parent->object->getId(),
                    $identifier);
                $this->ctrl->setParameterByClass('ilSelfEvaluationPresentationGUI', 'uid', $id->getId());
                $this->ctrl->redirectByClass('ilSelfEvaluationPresentationGUI', 'startScreen');
            } else {
                ilUtil::sendFailure($this->plugin->txt('uid_not_exists'), true);
                $this->ctrl->redirect($this, 'show');
            }
        }
        $this->ex->setValuesByPost();
        $this->tpl->setContent($this->ex->getHTML());
    }

    public function startWithNewUid()
    {
        $id = Identity::_getNewHashInstanceForObjId($this->db, $this->parent->object->getId());
        $this->ctrl->setParameterByClass('ilSelfEvaluationPresentationGUI', 'uid', $id->getId());
        $this->ctrl->redirectByClass('ilSelfEvaluationPresentationGUI', 'startScreen');
    }

    public function cancel()
    {
        $this->ctrl->redirect($this);
    }
}