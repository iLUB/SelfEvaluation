<?php
require_once __DIR__ . '/../vendor/autoload.php';

use ilub\plugin\SelfEvaluation\Block\BlockTableGUI;
use ilub\plugin\SelfEvaluation\Block\BlockFactory;

class ListBlocksGUI
{

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
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;
    /**
     * @var ilSelfEvaluationPlugin
     */
    protected $plugin;
    /**
     * @var ilAccessHandler
     */
    protected $access;
    /**
     * @var ilDBInterface
     */
    protected $db;

    public function __construct(
        ilDBInterface $db,
        ilObjSelfEvaluationGUI $parent,
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ilCtrl,
        ilToolbarGUI $ilToolbar,
        ilAccessHandler $access,
        ilSelfEvaluationPlugin $plugin
    ) {
        $this->db = $db;
        $this->ctrl = $ilCtrl;
        $this->tpl = $tpl;
        $this->parent = $parent;
        $this->toolbar = $ilToolbar;
        $this->access = $access;
        $this->plugin = $plugin;
    }

    public function executeCommand()
    {
        $this->ctrl->saveParameter($this, 'block_id');
        $this->performCommand();
    }

    function performCommand()
    {
        $cmd = ($this->ctrl->getCmd()) ? $this->ctrl->getCmd() : $this->getStandardCommand();

        switch ($cmd) {
            case 'showContent':
            case 'saveSorting':
            case 'editOverall':
                if (!$this->access->checkAccess("write", $cmd, $this->parent->object->getRefId(), $this->plugin->getId(),
                    $this->parent->object->getId())) {
                    throw new ilObjectException($this->plugin->txt("permission_denied"));
                }
                $this->$cmd();
                break;
        }
    }

    public function getStandardCommand() : string
    {
        return 'showContent';
    }

    public function showContent()
    {
        $this->tpl->addJavaScript($this->plugin->getDirectory() . '/templates/js/sortable.js');
        $table = new BlockTableGUI($this->ctrl, $this->plugin, $this->parent, 'showContent');



        $this->ctrl->setParameterByClass(QuestionBlockGUI::class, 'block_id', null);
        $this->toolbar->addButton($this->txt('add_new_question_block'),
            $this->ctrl->getLinkTargetByClass(QuestionBlockGUI::class, 'addBlock'));

        $this->ctrl->setParameterByClass(MetaBlockGUI::class, 'block_id', null);
        $this->toolbar->addButton($this->txt('add_new_meta_block'),
            $this->ctrl->getLinkTargetByClass(MetaBlockGUI::class, 'addBlock'));

        $this->ctrl->setParameterByClass(FeedbackGUI::class, 'parent_overall', 1);
        $this->toolbar->addButton($this->txt('edit_overal_feedback'),
            $this->ctrl->getLinkTargetByClass(FeedbackGUI::class, 'listObjects'));
        $this->ctrl->setParameterByClass(FeedbackGUI::class, 'parent_overall', 0);

        $factory = new BlockFactory($this->db, $this->getSelfEvalId());
        $blocks = $factory->getAllBlocks();

        $table_data = [];
        foreach ($blocks as $block) {
            $table_data[] = $block->getBlockTableRow($this->db, $this->ctrl, $this->plugin)->toArray();
        }

        $table->setData($table_data);
        $this->tpl->setContent($table->getHTML());
    }

    public function saveSorting()
    {
        $factory = new BlockFactory($this->db, $this->getSelfEvalId());
        $blocks = $factory->getAllBlocks();
        $positions = $_POST['position'];
        foreach ($blocks as $block) {
            $position = (int) array_search($block->getPositionId(), $positions) + 1;
            if ($position) {
                $block->setPosition($position);
                $block->update();
            }
        }

        ilUtil::sendSuccess($this->txt('sorting_saved'), true);
        $this->ctrl->redirect($this, 'showContent');
    }

    public function editOverall()
    {
        $this->tpl->setContent("hello World");
    }

    protected function getSelfEvalId() : int
    {
        return $this->parent->object->getId();
    }

    protected function txt(string $lng_var)
    {
        return $this->plugin->txt($lng_var);
    }
}