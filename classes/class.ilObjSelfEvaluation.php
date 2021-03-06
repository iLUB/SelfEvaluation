<?php
require_once __DIR__ . '/../vendor/autoload.php';

use ilub\plugin\SelfEvaluation\DatabaseHelper\hasDBFields;
use ilub\plugin\SelfEvaluation\DatabaseHelper\ArrayForDB;
use ilub\plugin\SelfEvaluation\UIHelper\Scale\Scale;
use ilub\plugin\SelfEvaluation\Block\Matrix\QuestionBlock;
use ilub\plugin\SelfEvaluation\Block\Meta\MetaBlock;
use ilub\plugin\SelfEvaluation\Identity\Identity;
use ilub\plugin\SelfEvaluation\Feedback\Feedback;
use ilub\plugin\SelfEvaluation\UIHelper\Scale\ScaleUnit;
use ilub\plugin\SelfEvaluation\Question\Matrix\Question;
use ilub\plugin\SelfEvaluation\Dataset\Data;
use ilub\plugin\SelfEvaluation\Dataset\Dataset;
use ilub\plugin\SelfEvaluation\Block\BlockFactory;

class ilObjSelfEvaluation extends ilObjectPlugin implements hasDBFields
{
    use ArrayForDB;

    const TABLE_NAME = 'rep_robj_xsev_data';
    const TYPE_GROUP = 1;
    const SHUFFLE_OFF = 1;
    const SHUFFLE_IN_BLOCKS = 2;
    const SHUFFLE_ACROSS_BLOCKS = 3;

    const DISPLAY_TYPE_SINGLE_PAGE = 1; // Single Page
    const DISPLAY_TYPE_MULTIPLE_PAGES = 2; // Multiple Pages
    /**
     * @var bool
     */
    protected $online = false;
    /**
     * @var int
     */
    protected $evaluation_type = self::TYPE_GROUP;
    /**
     * @var int
     */
    protected $sort_type = self::SHUFFLE_OFF;
    /**
     * @var int
     */
    protected $display_type = self::DISPLAY_TYPE_MULTIPLE_PAGES;
    /**
     * @var string
     */
    protected $intro = '';
    /**
     * @var string
     */
    protected $outro_title = '';
    /**
     * @var string
     */
    protected $outro = '';
    /**
     * @var bool
     */
    protected $identity_selection = true;

    /**
     * @var string
     */
    protected $identity_selection_info_text = '';
    /**
     * @var bool
     */
    protected $show_charts = true;
    /**
     * @var bool
     */
    protected $allow_multiple_datasets = true;
    /**
     * @var bool
     */
    protected $allow_dataset_editing = false;
    /**
     * @var bool
     */
    protected $allow_show_results = true;
    /**
     * @var bool
     */
    protected $show_feedbacks = true;
    /**
     * @var bool
     */
    protected $show_feedbacks_charts = true;
    /**
     * @var bool
     */
    protected $show_feedbacks_overview = true;
    /**
     * @var bool
     */
    protected $show_block_titles_during_evaluation = true;
    /**
     * @var bool
     */
    protected $show_block_descriptions_during_evaluation = true;
    /**
     * @var bool
     */
    protected $show_block_titles_during_feedback = true;
    /**
     * @var bool
     */
    protected $show_block_descriptions_during_feedback = true;

    /**
     * @var bool
     */
    protected $show_fbs_overview_bar = true;
    /**
     * @var bool
     */
    protected $show_fbs_overview_text = true;
    /**
     * @var bool
     */
    protected $show_fbs_overview_statistics = true;
    /**
     * @var bool
     */
    protected $show_fbs_overview_spider = true;
    /**
     * @var bool
     */
    protected $show_fbs_overview_left_right = true;
    /**
     * @var bool
     */
    protected $show_fbs_chart_bar = true;
    /**
     * @var bool
     */
    protected $show_fbs_chart_spider = true;
    /**
     * @var bool
     */
    protected $show_fbs_chart_left_right = true;

    /**
     * @var int
     */
    protected $sort_random_nr_item_block = 10;

    /**
     * @var bool
     */
    protected $overview_bar_show_label_as_percentage = false;

    /**
     * @var string
     */
    protected $block_option_random_desc = "";

    /**
     * @return array
     */
    protected function getNonDbFields()
    {
        return ['db'];
    }

    /**
     * @param                     $a_target_id
     * @param                     $a_copy_id
     * @param ilObjSelfEvaluation $new_obj
     */
    protected function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $new_obj->setOnline(false);
        $new_obj->setIdentitySelection($this->isIdentitySelection());
        $new_obj->setEvaluationType($this->getEvaluationType());
        $new_obj->setSortType($this->getSortType());
        $new_obj->setDisplayType($this->getDisplayType());
        $new_obj->setIntro($this->getIntro());
        $new_obj->setOutro($this->getOutro());
        $new_obj->setOutroTitle($this->getOutroTitle());
        $new_obj->setIdentitySelectionInfoText($this->getIdentitySelectionInfoText());
        $new_obj->setShowFeedbacks($this->isShowFeedbacks());
        $new_obj->setShowFeedbacksCharts($this->isShowFeedbacksCharts());
        $new_obj->setShowFeedbacksOverview($this->isShowFeedbacksOverview());
        $new_obj->setShowFbsOverviewStatistics($this->isShowFbsOverviewStatistics());
        $new_obj->setShowBlockTitlesDuringEvaluation($this->isShowBlockTitlesDuringEvaluation());
        $new_obj->setShowBlockDescriptionsDuringEvaluation($this->isShowBlockDescriptionsDuringEvaluation());
        $new_obj->setShowBlockTitlesDuringFeedback($this->isShowBlockTitlesDuringFeedback());
        $new_obj->setShowBlockDescriptionsDuringFeedback($this->isShowBlockDescriptionsDuringFeedback());
        $new_obj->setSortRandomNrItemBlock($this->getSortRandomNrItemBlock());
        $new_obj->setBlockOptionRandomDesc($this->getBlockOptionRandomDesc());
        $new_obj->setShowFbsOverviewBar($this->isShowFbsOverviewBar());
        $new_obj->setShowFbsOverviewText($this->isShowFbsOverviewText());
        $new_obj->setOverviewBarShowLabelAsPercentage($this->isOverviewBarShowLabelAsPercentage());
        $new_obj->setShowFbsOverviewSpider($this->isShowFbsOverviewSpider());
        $new_obj->setShowFbsOverviewLeftRight($this->isShowFbsOverviewLeftRight());
        $new_obj->setShowFbsChartBar($this->isShowFbsChartBar());
        $new_obj->setShowFbsChartSpider($this->isShowFbsChartSpider());
        $new_obj->setShowFbsChartLeftRight($this->isShowFbsChartLeftRight());
        $new_obj->update();

        //Copy Scale
        $old_scale = Scale::_getInstanceByObjId($this->db, $this->getId());
        $old_scale->cloneTo($new_obj->getId());

        //Copy Blocks
        $block_factory = new BlockFactory($this->db, $this->getId());
        foreach ($block_factory->getAllBlocks() as $block) {
            $block->cloneTo($new_obj->getId());
        }

        //Copy Overall Feedback
        $old_feedbacks = Feedback::_getAllInstancesForParentId($this->db, $this->getId(), false, true);
        foreach ($old_feedbacks as $feedback) {
            $feedback->cloneTo($new_obj->getId());
        }

    }

    final function initType()
    {
        $this->setType('xsev');
    }

    function doCreate()
    {
        /** @var ilSelfEvaluationPlugin $plugin */
        $plugin = $this->plugin;
        $config = new ilSelfEvaluationConfig($plugin->getConfigTableName());
        $this->setIdentitySelectionInfoText($config->getValue('identity_selection'));
        $this->setOutroTitle($this->txt('outro_header'));
        $this->db->insert(self::TABLE_NAME, $this->getArrayForDb());
    }

    public function getArrayForDb() : array
    {
        return [
            'id' => [
                'integer',
                $this->getId()
            ],
            'is_online' => [
                'integer',
                $this->isOnline()
            ],
            'identity_selection' => [
                'integer',
                $this->isIdentitySelection()
            ],
            'evaluation_type' => [
                'integer',
                $this->getEvaluationType()
            ],
            'sort_type' => [
                'integer',
                $this->getSortType()
            ],
            'display_type' => [
                'integer',
                $this->getDisplayType()
            ],
            'intro' => [
                'text',
                $this->getIntro()
            ],
            'outro_title' => [
                'text',
                $this->getOutroTitle()
            ],
            'outro' => [
                'text',
                $this->getOutro()
            ],
            'identity_selection_info' => [
                'text',
                $this->getIdentitySelectionInfoText()
            ],
            'show_fbs' => [
                'integer',
                $this->isShowFeedbacks()
            ],
            'show_fbs_charts' => [
                'integer',
                $this->isShowFeedbacksCharts()
            ],
            'show_fbs_overview' => [
                'integer',
                $this->isShowFeedbacksOverview()
            ],
            'show_fbs_overview_text' => [
                'integer',
                $this->isShowFbsOverviewText()
            ],
            'show_fbs_overview_statistics' => [
                'integer',
                $this->isShowFbsOverviewStatistics()
            ],
            'show_block_titles_sev' => [
                'integer',
                $this->isShowBlockTitlesDuringEvaluation()
            ],
            'show_block_desc_sev' => [
                'integer',
                $this->isShowBlockDescriptionsDuringEvaluation()
            ],
            'show_block_titles_fb' => [
                'integer',
                $this->isShowBlockTitlesDuringFeedback()
            ],
            'show_block_desc_fb' => [
                'integer',
                $this->isShowBlockDescriptionsDuringFeedback()
            ],
            'sort_random_nr_items_block' => [
                'integer',
                $this->getSortRandomNrItemBlock()
            ],
            'block_option_random_desc' => [
                'text',
                $this->getBlockOptionRandomDesc()
            ],
            'show_fbs_overview_bar' => [
                'integer',
                $this->isShowFbsOverviewBar()
            ],
            'bar_show_label_as_percentage' => [
                'integer',
                $this->isOverviewBarShowLabelAsPercentage()
            ],
            'show_fbs_overview_spider' => [
                'integer',
                $this->isShowFbsOverviewSpider()
            ],
            'show_fbs_overview_left_right' => [
                'integer',
                $this->isShowFbsOverviewLeftRight()
            ],
            'show_fbs_chart_bar' => [
                'integer',
                $this->isShowFbsChartBar()
            ],
            'show_fbs_chart_spider' => [
                'integer',
                $this->isShowFbsChartSpider()
            ],
            'show_fbs_chart_left_right' => [
                'integer',
                $this->isShowFbsChartLeftRight()
            ],
        ];
    }

    /**
     * @return bool
     */
    public function isOnline() : bool
    {
        return $this->online;
    }

    /**
     * @param bool $online
     */
    public function setOnline(bool $online) : void
    {
        $this->online = $online;
    }

    /**
     * @return int
     */
    public function getEvaluationType() : int
    {
        return $this->evaluation_type;
    }

    /**
     * @param int $evaluation_type
     */
    public function setEvaluationType(int $evaluation_type) : void
    {
        $this->evaluation_type = $evaluation_type;
    }

    /**
     * @return int
     */
    public function getSortType() : int
    {
        return $this->sort_type;
    }

    /**
     * @param int $sort_type
     */
    public function setSortType(int $sort_type) : void
    {
        $this->sort_type = $sort_type;
    }

    /**
     * @return int
     */
    public function getDisplayType() : int
    {
        return $this->display_type;
    }

    /**
     * @param int $display_type
     */
    public function setDisplayType(int $display_type) : void
    {
        $this->display_type = $display_type;
    }

    /**
     * @return string
     */
    public function getIntro() : string
    {
        return $this->intro;
    }

    /**
     * @param string $intro
     */
    public function setIntro(string $intro) : void
    {
        $this->intro = $intro;
    }

    /**
     * @return string
     */
    public function getOutroTitle() : string
    {
        return $this->outro_title;
    }

    /**
     * @param string $outro_title
     */
    public function setOutroTitle(string $outro_title) : void
    {
        $this->outro_title = $outro_title;
    }

    /**
     * @return string
     */
    public function getOutro() : string
    {
        return $this->outro;
    }

    /**
     * @param string $outro
     */
    public function setOutro(string $outro) : void
    {
        $this->outro = $outro;
    }

    /**
     * @return bool
     */
    public function isIdentitySelection() : bool
    {
        return $this->identity_selection;
    }

    /**
     * @param bool $identity_selection
     */
    public function setIdentitySelection(bool $identity_selection) : void
    {
        $this->identity_selection = $identity_selection;
    }

    /**
     * @return string
     */
    public function getIdentitySelectionInfoText() : string
    {
        return $this->identity_selection_info_text;
    }

    /**
     * @param string $identity_selection_info_text
     */
    public function setIdentitySelectionInfoText(string $identity_selection_info_text) : void
    {
        $this->identity_selection_info_text = $identity_selection_info_text;
    }

    /**
     * @return bool
     */
    public function isShowFeedbacks() : bool
    {
        return $this->show_feedbacks;
    }

    /**
     * @param bool $show_feedbacks
     */
    public function setShowFeedbacks(bool $show_feedbacks) : void
    {
        $this->show_feedbacks = $show_feedbacks;
    }

    /**
     * @return bool
     */
    public function isShowFeedbacksCharts() : bool
    {
        return $this->show_feedbacks_charts;
    }

    /**
     * @param bool $show_feedbacks_charts
     */
    public function setShowFeedbacksCharts(bool $show_feedbacks_charts) : void
    {
        $this->show_feedbacks_charts = $show_feedbacks_charts;
    }

    /**
     * @return bool
     */
    public function isShowFeedbacksOverview() : bool
    {
        return $this->show_feedbacks_overview;
    }

    /**
     * @param bool $show_feedbacks_overview
     */
    public function setShowFeedbacksOverview(bool $show_feedbacks_overview) : void
    {
        $this->show_feedbacks_overview = $show_feedbacks_overview;
    }

    /**
     * @return bool
     */
    public function isShowBlockTitlesDuringEvaluation() : bool
    {
        return $this->show_block_titles_during_evaluation;
    }

    /**
     * @param bool $show_block_titles_during_evaluation
     */
    public function setShowBlockTitlesDuringEvaluation(bool $show_block_titles_during_evaluation) : void
    {
        $this->show_block_titles_during_evaluation = $show_block_titles_during_evaluation;
    }

    /**
     * @return bool
     */
    public function isShowBlockDescriptionsDuringEvaluation() : bool
    {
        return $this->show_block_descriptions_during_evaluation;
    }

    /**
     * @param bool $show_block_descriptions_during_evaluation
     */
    public function setShowBlockDescriptionsDuringEvaluation(bool $show_block_descriptions_during_evaluation) : void
    {
        $this->show_block_descriptions_during_evaluation = $show_block_descriptions_during_evaluation;
    }

    /**
     * @return bool
     */
    public function isShowBlockTitlesDuringFeedback() : bool
    {
        return $this->show_block_titles_during_feedback;
    }

    /**
     * @param bool $show_block_titles_during_feedback
     */
    public function setShowBlockTitlesDuringFeedback(bool $show_block_titles_during_feedback) : void
    {
        $this->show_block_titles_during_feedback = $show_block_titles_during_feedback;
    }

    /**
     * @return bool
     */
    public function isShowBlockDescriptionsDuringFeedback() : bool
    {
        return $this->show_block_descriptions_during_feedback;
    }

    /**
     * @param bool $show_block_descriptions_during_feedback
     */
    public function setShowBlockDescriptionsDuringFeedback(bool $show_block_descriptions_during_feedback) : void
    {
        $this->show_block_descriptions_during_feedback = $show_block_descriptions_during_feedback;
    }

    /**
     * @return bool
     */
    public function isShowFbsOverviewBar() : bool
    {
        return $this->show_fbs_overview_bar;
    }

    /**
     * @param bool $show_fbs_overview_bar
     */
    public function setShowFbsOverviewBar(bool $show_fbs_overview_bar) : void
    {
        $this->show_fbs_overview_bar = $show_fbs_overview_bar;
    }

    /**
     * @return bool
     */
    public function isShowFbsOverviewText() : bool
    {
        return $this->show_fbs_overview_text;
    }

    /**
     * @param bool $show_fbs_overview_text
     */
    public function setShowFbsOverviewText(bool $show_fbs_overview_text) : void
    {
        $this->show_fbs_overview_text = $show_fbs_overview_text;
    }

    /**
     * @return bool
     */
    public function isShowFbsOverviewStatistics() : bool
    {
        return $this->show_fbs_overview_statistics;
    }

    /**
     * @param bool $show_fbs_overview_statistics
     */
    public function setShowFbsOverviewStatistics(bool $show_fbs_overview_statistics) : void
    {
        $this->show_fbs_overview_statistics = $show_fbs_overview_statistics;
    }

    /**
     * @return bool
     */
    public function isShowFbsOverviewSpider() : bool
    {
        return $this->show_fbs_overview_spider;
    }

    /**
     * @param bool $show_fbs_overview_spider
     */
    public function setShowFbsOverviewSpider(bool $show_fbs_overview_spider) : void
    {
        $this->show_fbs_overview_spider = $show_fbs_overview_spider;
    }

    /**
     * @return bool
     */
    public function isShowFbsOverviewLeftRight() : bool
    {
        return $this->show_fbs_overview_left_right;
    }

    /**
     * @param bool $show_fbs_overview_left_right
     */
    public function setShowFbsOverviewLeftRight(bool $show_fbs_overview_left_right) : void
    {
        $this->show_fbs_overview_left_right = $show_fbs_overview_left_right;
    }

    /**
     * @return bool
     */
    public function isShowFbsChartBar() : bool
    {
        return $this->show_fbs_chart_bar;
    }

    /**
     * @param bool $show_fbs_chart_bar
     */
    public function setShowFbsChartBar(bool $show_fbs_chart_bar) : void
    {
        $this->show_fbs_chart_bar = $show_fbs_chart_bar;
    }

    /**
     * @return bool
     */
    public function isShowFbsChartSpider() : bool
    {
        return $this->show_fbs_chart_spider;
    }

    /**
     * @param bool $show_fbs_chart_spider
     */
    public function setShowFbsChartSpider(bool $show_fbs_chart_spider) : void
    {
        $this->show_fbs_chart_spider = $show_fbs_chart_spider;
    }

    /**
     * @return bool
     */
    public function isShowFbsChartLeftRight() : bool
    {
        return $this->show_fbs_chart_left_right;
    }

    /**
     * @param bool $show_fbs_chart_left_right
     */
    public function setShowFbsChartLeftRight(bool $show_fbs_chart_left_right) : void
    {
        $this->show_fbs_chart_left_right = $show_fbs_chart_left_right;
    }

    /**
     * @return int
     */
    public function getSortRandomNrItemBlock() : int
    {
        return $this->sort_random_nr_item_block;
    }

    /**
     * @param int $sort_random_nr_item_block
     */
    public function setSortRandomNrItemBlock(int $sort_random_nr_item_block) : void
    {
        $this->sort_random_nr_item_block = $sort_random_nr_item_block;
    }

    /**
     * @return bool
     */
    public function isOverviewBarShowLabelAsPercentage() : bool
    {
        return $this->overview_bar_show_label_as_percentage;
    }

    /**
     * @param bool $overview_bar_show_label_as_percentage
     */
    public function setOverviewBarShowLabelAsPercentage(bool $overview_bar_show_label_as_percentage) : void
    {
        $this->overview_bar_show_label_as_percentage = $overview_bar_show_label_as_percentage;
    }

    /**
     * @return string
     */
    public function getBlockOptionRandomDesc() : string
    {
        return $this->block_option_random_desc;
    }

    /**
     * @param string $block_option_random_desc
     */
    public function setBlockOptionRandomDesc(string $block_option_random_desc) : void
    {
        $this->block_option_random_desc = $block_option_random_desc;
    }



    function doRead()
    {
        $set = $this->db->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE id = '
            . $this->db->quote($this->getId(), 'integer'));
        while ($rec = $this->db->fetchObject($set)) {
            $this->setOnline($rec->is_online);
            $this->setIdentitySelection((bool)$rec->identity_selection);
            $this->setEvaluationType((int)$rec->evaluation_type);
            $this->setSortType((int)$rec->sort_type);
            $this->setDisplayType((int)$rec->display_type);
            $this->setIntro((string)$rec->intro);
            $this->setOutro((string)$rec->outro);
            $this->setOutroTitle((string)$rec->outro_title);
            $this->setIdentitySelectionInfoText((string)$rec->identity_selection_info);
            $this->setShowFeedbacks((bool)$rec->show_fbs);
            $this->setShowFeedbacksCharts((bool)$rec->show_fbs_charts);
            $this->setShowFeedbacksOverview((bool)$rec->show_fbs_overview);
            $this->setShowFbsOverviewText((bool)$rec->show_fbs_overview_text);
            $this->setShowFbsOverviewStatistics((bool)$rec->show_fbs_overview_statistics);
            $this->setShowBlockTitlesDuringEvaluation((bool)$rec->show_block_titles_sev);
            $this->setShowBlockDescriptionsDuringEvaluation((bool)$rec->show_block_desc_sev);
            $this->setShowBlockTitlesDuringFeedback((bool)$rec->show_block_titles_fb);
            $this->setShowBlockDescriptionsDuringFeedback((bool)$rec->show_block_desc_fb);
            $this->setSortRandomNrItemBlock((int)$rec->sort_random_nr_items_block);
            $this->setBlockOptionRandomDesc((string)$rec->block_option_random_desc);
            $this->setShowFbsOverviewBar((bool)$rec->show_fbs_overview_bar);
            $this->setOverviewBarShowLabelAsPercentage((bool)$rec->bar_show_label_as_percentage);
            $this->setShowFbsOverviewSpider((bool)$rec->show_fbs_overview_spider);
            $this->setShowFbsOverviewLeftRight((bool)$rec->show_fbs_overview_left_right);
            $this->setShowFbsChartBar((bool)$rec->show_fbs_chart_bar);
            $this->setShowFbsChartSpider((bool)$rec->show_fbs_chart_spider);
            $this->setShowFbsChartLeftRight((bool)$rec->show_fbs_chart_left_right);

        }
    }

    function doUpdate()
    {

        $this->db->update(self::TABLE_NAME, $this->getArrayForDb(), $this->getIdForDb());
    }

    public function doDelete()
    {
        $scale = Scale::_getInstanceByObjId($this->db, $this->getId());
        foreach (ScaleUnit::_getAllInstancesByParentId($this->db, $scale->getId()) as $u) {
            $u->delete();
        }
        $scale->delete();
        foreach (Identity::_getAllInstancesByObjId($this->db, $this->getId()) as $id) {
            foreach (Dataset::_getAllInstancesByIdentifierId($this->db, $id->getId()) as $ds) {
                foreach (Data::_getAllInstancesByDatasetId($this->db, $ds->getId()) as $d) {
                    $d->delete();
                }
                $ds->delete();
            }
            $id->delete();
        }
        foreach (QuestionBlock::_getAllInstancesByParentId($this->db, $this->getId()) as $block) {
            foreach (Question::_getAllInstancesForParentId($this->db, $block->getId()) as $qst) {
                $qst->delete();
            }
            foreach (Feedback::_getAllInstancesForParentId($this->db, $block->getId()) as $fb) {
                $fb->delete();
            }
            $block->delete();
        }
        foreach (MetaBlock::_getAllInstancesByParentId($this->db, $this->getId()) as $block) {
            $block->delete();
        }
        $this->db->manipulate('DELETE FROM ' . self::TABLE_NAME . ' WHERE ' . ' id = '
            . $this->db->quote($this->getId(), 'integer'));

        return true;
    }

    public function toXML()
    {
        $xml = new SimpleXMLElement('<SelfEvaluation/>');
        $xml->addAttribute("xmlns", "http://www.w3.org");
        $xml->addAttribute("title", $this->getTitle());
        $xml->addAttribute("description", $this->getDescription());
        $xml->addAttribute("online", $this->isOnline());
        $xml->addAttribute("identitySelection", $this->isIdentitySelection());
        $xml->addAttribute("evaluationType", $this->getEvaluationType());
        $xml->addAttribute("sortType", $this->getSortType());
        $xml->addAttribute("displayType", $this->getDisplayType());
        $xml->addAttribute("intro", $this->getIntro());
        $xml->addAttribute("outro", $this->getOutro());
        $xml->addAttribute("outroTitle", $this->getOutroTitle());
        $xml->addAttribute("identitySelectionInfoText", $this->getIdentitySelectionInfoText());
        $xml->addAttribute("showFeedbacks", $this->isShowFeedbacks());
        $xml->addAttribute("showFeedbacksCharts", $this->isShowFeedbacksCharts());
        $xml->addAttribute("showFeedbacksOverview", $this->isShowFeedbacksOverview());
        $xml->addAttribute("showFbsOverviewStatistics", $this->isShowFbsOverviewStatistics());
        $xml->addAttribute("showBlockTitlesDuringEvaluation", $this->isShowBlockTitlesDuringEvaluation());
        $xml->addAttribute("showBlockDescriptionsDuringEvaluation", $this->isShowBlockDescriptionsDuringEvaluation());
        $xml->addAttribute("showBlockTitlesDuringFeedback", $this->isShowBlockTitlesDuringFeedback());
        $xml->addAttribute("showBlockDescriptionsDuringFeedback", $this->isShowBlockDescriptionsDuringFeedback());
        $xml->addAttribute("sortRandomNrItemBlock", $this->getSortRandomNrItemBlock());
        $xml->addAttribute("blockOptionRandomDesc", $this->getBlockOptionRandomDesc());
        $xml->addAttribute("showFbsOverviewBar", $this->isShowFbsOverviewBar());
        $xml->addAttribute("showFbsOverviewText", $this->isShowFbsOverviewText());
        $xml->addAttribute("overviewBarShowLabelAsPercentage", $this->isOverviewBarShowLabelAsPercentage());
        $xml->addAttribute("showFbsOverviewSpider", $this->isShowFbsOverviewSpider());
        $xml->addAttribute("showFbsOverviewLeftRight", $this->isShowFbsOverviewLeftRight());
        $xml->addAttribute("showFbsChartBar", $this->isShowFbsChartBar());
        $xml->addAttribute("showFbsChartSpider", $this->isShowFbsChartSpider());
        $xml->addAttribute("showFbsChartLeftRight", $this->isShowFbsChartLeftRight());

        //Export Scale
        $scale = Scale::_getInstanceByObjId($this->db, $this->getId());
        $xml = $scale->toXML($xml);

        //Export Blocks
        $block_factory = new BlockFactory($this->db, $this->getId());
        foreach ($block_factory->getAllBlocks() as $block) {
            $xml = $block->toXML($xml);
        }

        //Export Overall Feedback
        $feedbacks = Feedback::_getAllInstancesForParentId($this->db, $this->getId(), false, true);
        foreach ($feedbacks as $feedback) {
            $xml = $feedback->toXML($xml);
        }
        return $xml;

    }

    public function fromXML(string $xml)
    {

        if (!$this->getId()) {
            $this->create();
            $this->createReference();
        }

        $xml = new SimpleXMLElement($xml);
        $xml_attributes = $xml->attributes();

        $this->setTitle($xml_attributes["title"]);
        $this->setDescription($xml_attributes["description"]);
        $this->setOnline(false);
        $this->setIdentitySelection($xml_attributes["identitySelection"] == "1");
        $this->setEvaluationType((int) $xml_attributes["evaluationType"]);
        $this->setSortType((int) $xml_attributes["sortType"]);
        $this->setDisplayType((int) $xml_attributes["displayType"]);
        $this->setIntro($xml_attributes["intro"]);
        $this->setOutro($xml_attributes["outro"]);
        $this->setOutroTitle($xml_attributes["outroTitle"]);
        $this->setIdentitySelectionInfoText((string) $xml_attributes["identitySelectionInfoText"]);
        $this->setShowFeedbacks( $xml_attributes["showFeedbacks"] == "1");
        $this->setShowFeedbacksCharts($xml_attributes["showFeedbacksCharts"] == "1");
        $this->setShowFeedbacksOverview($xml_attributes["showFeedbacksOverview"] == "1");
        $this->setShowFbsOverviewStatistics( $xml_attributes["showFbsOverviewStatistics"] == "1");
        $this->setShowBlockTitlesDuringEvaluation( $xml_attributes["showBlockTitlesDuringEvaluation"] == "1");
        $this->setShowBlockDescriptionsDuringEvaluation( $xml_attributes["showBlockDescriptionsDuringEvaluation"] == "1");
        $this->setShowBlockTitlesDuringFeedback( $xml_attributes["showBlockTitlesDuringFeedback"] == "1");
        $this->setShowBlockDescriptionsDuringFeedback($xml_attributes["showBlockDescriptionsDuringFeedback"] == "1");
        $this->setSortRandomNrItemBlock((int)$xml_attributes["sortRandomNrItemBlock"] );
        $this->setBlockOptionRandomDesc( (string)$xml_attributes["blockOptionRandomDesc"]);
        $this->setShowFbsOverviewBar($xml_attributes["showFbsOverviewBar"] == "1");
        $this->setShowFbsOverviewText($xml_attributes["showFbsOverviewText"] == "1");
        $this->setOverviewBarShowLabelAsPercentage( $xml_attributes["overviewBarShowLabelAsPercentage"] == "1");
        $this->setShowFbsOverviewSpider($xml_attributes["showFbsOverviewSpider"] == "1");
        $this->setShowFbsOverviewLeftRight($xml_attributes["showFbsOverviewLeftRight"] == "1");
        $this->setShowFbsChartBar($xml_attributes["showFbsChartBar"] == "1");
        $this->setShowFbsChartSpider( $xml_attributes["showFbsChartSpider"] == "1");
        $this->setShowFbsChartLeftRight($xml_attributes["showFbsChartLeftRight"] == "1");
        $this->update();

        //Import Scale
        if ($xml->scale) {
            Scale::fromXml($this->db, $this->getId(), $xml->scale);
        }

        //Import Blocks
        foreach ($xml->metaBlock as $block) {
            MetaBlock::fromXml($this->db, $this->getId(), $block);
        }
        foreach ($xml->questionBlock as $block) {
            QuestionBlock::fromXml($this->db, $this->getId(), $block);
        }

        //Import Overall Feedback
        foreach ($xml->feedback as $feedback) {
            Feedback::fromXml($this->db, $this->getId(), $feedback);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive() : bool
    {
        return ((bool) $this->isOnline() AND $this->hasBlocks() AND $this->areFeedbacksComplete() AND $this->hasScale()) ? true : false;
    }

    public function hasBLocks() : bool
    {
        foreach (QuestionBlock::_getAllInstancesByParentId($this->db, $this->getId()) as $block) {
            if (count(Question::_getAllInstancesForParentId($this->db, $block->getId())) > 0) {
                return true;
            }
        }
        foreach (MetaBlock::_getAllInstancesByParentId($this->db, $this->getId()) as $block) {
            if (count($block->getQuestions()) > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function areFeedbacksComplete() : bool
    {
        $return = true;
        foreach (QuestionBlock::_getAllInstancesByParentId($this->db, $this->getId()) as $block) {
            $return = Feedback::_isComplete($this->db, $block->getId()) ? $return : false;
        }

        return $return;
    }

    /**
     * @return bool
     */
    public function hasScale() : bool
    {
        return Scale::_getInstanceByObjId($this->db, $this->getId())->hasUnits();
    }

    /**
     * @return bool
     */
    public function hasDatasets() : bool
    {
        foreach (Identity::_getAllInstancesByObjId($this->db, $this->getId()) as $id) {
            if (count(Dataset::_getAllInstancesByIdentifierId($this->db, $id->getId())) > 0) {
                return true;
            }
        }

        return false;
    }

    public function areBlocksSortable() : bool
    {
        switch ($this->getSortType()) {
            case self::SHUFFLE_OFF:
                return true;
            case self::SHUFFLE_IN_BLOCKS:
                return false;
            default:
                return false;
        }
    }

    /**
     * @return bool
     */
    public function isShowCharts() : bool
    {
        return $this->show_charts;
    }

    /**
     * @param bool $show_charts
     */
    public function setShowCharts(bool $show_charts) : void
    {
        $this->show_charts = $show_charts;
    }

    /**
     * @return bool
     */
    public function isAllowMultipleDatasets() : bool
    {
        return $this->allow_multiple_datasets;
    }

    /**
     * @param bool $allow_multiple_datasets
     */
    public function setAllowMultipleDatasets(bool $allow_multiple_datasets) : void
    {
        $this->allow_multiple_datasets = $allow_multiple_datasets;
    }

    /**
     * @return bool
     */
    public function isAllowDatasetEditing() : bool
    {
        return $this->allow_dataset_editing;
    }

    /**
     * @param bool $allow_dataset_editing
     */
    public function setAllowDatasetEditing(bool $allow_dataset_editing) : void
    {
        $this->allow_dataset_editing = $allow_dataset_editing;
    }

    /**
     * @return bool
     */
    public function isAllowShowResults() : bool
    {
        return $this->allow_show_results;
    }

    /**
     * @param bool $allow_show_results
     */
    public function setAllowShowResults(bool $allow_show_results) : void
    {
        $this->allow_show_results = $allow_show_results;
    }

}