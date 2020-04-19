<?php

namespace ilub\plugin\SelfEvaluation\Question;

use iLubFieldDefinitionContainerGUI;
use ilSelfEvaluationPlugin;
use ilToolbarGUI;
use ilTemplate;
use ilCtrl;

class MetaQuestionGUI extends iLubFieldDefinitionContainerGUI
{

    const POSTVAR_PREFIX = 'mqst_';
    /**
     * @var ilSelfEvaluationPlugin
     */
    protected $plugin;
    /**
     * @var string
     */
    protected $block_title;

    protected $toolbar;

    /**
     * @param iLubFieldDefinitionContainer $container
     * @param string                       $block_title
     * @param ilSelfEvaluationPlugin       $plugin
     * @param int                          $self_eval_id
     */
    public function __construct(
        iLubFieldDefinitionContainer $container,
        string $block_title,
        ilSelfEvaluationPlugin $plugin,
        int $self_eval_id,
        ilToolbarGUI $toolbar,
        ilTemplate $tpl,
        ilCtrl $ctrl
    ) {
        $this->plugin = $plugin;
        $this->block_title = $block_title;
        $this->toolbar = $toolbar;

        parent::__construct($container, self::getTypesArray(), new iLubFieldDefinitionLng(), $self_eval_id,$tpl, $ctrl);
    }

    public function executeCommand()
    {
        $this->ctrl->saveParameter($this, 'block_id');
        $this->tabs_gui->setTabActive('administration');
        parent::executeCommand();
    }

    protected function listFields()
    {
        $this->toolbar->addButton('<b>&lt;&lt; ' . $this->plugin->txt('back_to_blocks') . '</b>',
            $this->ctrl->getLinkTargetByClass('ilSelfEvaluationListBlocksGUI', 'showContent'));
        parent::listFields();
        $this->tpl->addJavaScript($this->plugin->getDirectory() . '/templates/sortable.js');
    }

    /**
     * @return iLubFieldDefinitionTableGUI
     */
    protected function createILubFieldDefinitionTableGUI()
    {
        $table = parent::createILubFieldDefinitionTableGUI();
        $table->setTitle($this->block_title . ': ' . $this->plugin->txt('question_table_title'));

        return $table;
    }

    static function getTypesArray()
    {
        // Add the allowed types here
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/iLubFieldDefinition/classes/types/class.iLubFieldDefinitionTypeText.php');
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/iLubFieldDefinition/classes/types/class.iLubFieldDefinitionTypeSelect.php');
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/iLubFieldDefinition/classes/types/class.iLubFieldDefinitionTypeSingleChoice.php');
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/iLubFieldDefinition/classes/types/class.iLubFieldDefinitionTypeMatrix.php');

        $types[] = new iLubFieldDefinitionTypeText();
        $types[] = new iLubFieldDefinitionTypeSelect();
        $types[] = new iLubFieldDefinitionTypeSingleChoice();
        $types[] = new iLubFieldDefinitionTypeMatrix();

        /*
         * TODO add a radio button type for the gender, a text-area type for arbitrarily long text and a select-year-of-birth type
         * -> create new iLubFieldDefinitionTypeXYZ objects and add them here
         * @see http://ilublx3.unibe.ch:8080/mantis/view.php?id=514#c928
         */
        return $types;
    }
} 