<?php

namespace ilub\plugin\SelfEvaluation\Feedback;

use ilGlobalPageTemplate;
use ilRepositoryObjectPlugin;
use ilDBInterface;
use ilToolbarGUI;
use ilub\plugin\SelfEvaluation\Dataset\Dataset;
use ilButton;
use ilObjSelfEvaluation;
use ilSelfEvaluationQuestionBlock;
use ilTemplate;
use ilub\plugin\SelfEvaluation\UIHelper\Scale\Scale;
use ilub\plugin\SelfEvaluation\UIHelper\Chart\SpiderChart;
use ilub\plugin\SelfEvaluation\UIHelper\Chart\BarChart;
use ilub\plugin\SelfEvaluation\UIHelper\Chart\LeftRightChart;
use ilub\plugin\SelfEvaluation\Question\Question;

class FeedbackChartGUI
{
    /**
     * @var ilGlobalPageTemplate
     */
    protected $tpl;
    /**
     * @var ilRepositoryObjectPlugin
     */
    protected $plugin;
    /**
     * @var ilDBInterface
     */
    protected $db;
    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilObjSelfEvaluation
     */
    protected $evaluation;

    public function __construct(
        ilDBInterface $db,
        ilGlobalPageTemplate $tpl,
        ilRepositoryObjectPlugin $plugin,
        ilToolbarGUI $toolbar,
        ilObjSelfEvaluation $evaluation
    ) {
        $this->tpl = $tpl;
        $this->plugin = $plugin;
        $this->db = $db;
        $this->toolbar = $toolbar;
        $this->evaluation = $evaluation;
    }

    public function getPresentationOfFeedback(Dataset $data_set)
    {
        $tpl = $this->initTemplate();

        $percentages = $data_set->getPercentagePerBlock();
        $blocks = [];
        foreach ($data_set->getFeedbacksPerBlock() as $block_id => $feedback) {
            $block = new ilSelfEvaluationQuestionBlock($block_id);
            $this->parseBlockFeedback($tpl, $block, $feedback, $data_set);
            $blocks[] = [
                'block_id' => $block->getId(),
                'percentage' => $percentages[$block->getId()],
                'label' => $this->getBlockLabel($block)
            ];
        }

        if (count($data_set->getFeedbacksPerBlock()) > 0 AND $this->showOverview()) {
            $tpl->setCurrentBlock('overview');

            $tpl->setVariable('BLOCK_OVERVIEW_TITLE', $this->plugin->txt('block_overview_title'));

            $mean = $data_set->getOverallPercentage();
            $min = $data_set->getMinPercentageBlock();
            $max = $data_set->getMaxPercentageBlock();
            $sd_per_block = $data_set->getStandardabweichungPerBlock();
            $scale_max = $data_set->getHighestValueFromScale();

            $statistics_median = $this->plugin->txt("overview_statistics_median") . " " . round($scale_max * $mean / 100,
                    2);//." (".$mean."%)";
            $statistics_max = $this->plugin->txt("overview_statistics_max") . " " . $max['block']->getTitle() . ": " . round($scale_max * $max['percentage'] / 100,
                    2);// ." (".$max['percentage']."%)";
            $statistics_min = $this->plugin->txt("overview_statistics_min") . " " . $min['block']->getTitle() . ": " . round($scale_max * $min['percentage'] / 100,
                    2);// ." (".$min['percentage']."%)";

            $statistics_sd_per_block = $this->plugin->txt("overview_statistics_standardabweichung_per_plock") . ": ";
            foreach ($sd_per_block as $key => $sd) {
                $statistics_sd_per_block .= $data_set->getBlockById($key)->getTitle() . ": " . $sd . "; ";
            }

            if ($this->evaluation->isShowFbsOverviewStatistics()) {
                $tpl->setVariable('OVERVIEW_STATISTICS_TITLE', $this->plugin->txt("overview_statistics_title"));

                $tpl->setVariable('OVERVIEW_STATISTICS_MEDIAN', $statistics_median);
                $tpl->setVariable('OVERVIEW_STATISTICS_MAX', $statistics_max);
                $tpl->setVariable('OVERVIEW_STATISTICS_MIN', $statistics_min);

                //$varianz = $data_set->getOverallVarianz();
                //$standardabweichung = $data_set->getOverallStandardabweichung();
                //$statistics_varianz = $this->plugin->txt("overview_statistics_varianz") . ": " . $varianz;
                //$statistics_standardabweichung = $this->plugin->txt("overview_statistics_standardabweichung") . ": " . $standardabweichung;
                //$tpl->setVariable('OVERVIEW_VARIANZ', $statistics_varianz);
                //$tpl->setVariable('OVERVIEW_STANDARDABWEICHUNG', $statistics_standardabweichung);
                //$tpl->setVariable('OVERVIEW_STANDARDABWEICHUNG_PER_BLOCK', $statistics_sd_per_block);
            }

            if ($this->evaluation->isShowFbsOverviewBar()) {
                $tpl->setVariable('SHOW_BAR_CHART', $this->plugin->txt('show_bar_chart'));
                $chart = $this->getOverviewBarChart($blocks, $data_set->getOverallPercentage());
                if ($this->evaluation->isShowFbsOverviewStatistics()) {
                    $chart->setShowVarianz(false);
                    $chart->setStandardabweichungData($sd_per_block);
                    $chart->setValuesForStandardabweichung($data_set->getPercentagePerBlock());
                }
                $tpl->setVariable('OVERVIEW_BAR_CHART', $chart->getHTML());
            }
            if ($this->evaluation->isShowFbsOverviewSpider()) {
                $tpl->setVariable('OVERVIEW_SPIDER_CHART', $this->getOverviewSpiderChart($blocks)->getHTML());
                $tpl->setVariable('SHOW_SPIDER_CHART', $this->plugin->txt('show_spider_chart'));
            }
            if ($this->evaluation->isShowFbsOverviewLeftRight()) {
                $tpl->setVariable('OVERVIEW_LEFT_RIGHT_CHART', $this->getOverviewLeftRightChart($blocks)->getHTML());
                $tpl->setVariable('SHOW_LEFT_RIGHT_CHART', $this->plugin->txt('show_left_right_chart'));
            }

            if ($this->evaluation->isShowFbsOverviewText()) {
                $feedback = Feedback::_getFeedbackForPercentage($this->db,$this->evaluation->getId(), $mean);;
                if ($feedback) {
                    $tpl->setVariable('FEEDBACK_OVERVIEW_TITLE', $feedback->getTitle());
                    $tpl->setVariable('FEEDBACK_OVERVIEW_BODY', $feedback->getFeedbackText());
                }
            }
            $tpl->parseCurrentBlock();

        }

        return $tpl->get();
    }

    protected function showAnyFeedbackCharts()
    {
        $any_active = $this->evaluation->isShowFbsChartBar() || $this->evaluation->isShowFbsChartSpider() || $this->evaluation->isShowFbsChartLeftRight();
        return $this->evaluation->getShowFeedbacksCharts() && $any_active;
    }

    protected function showOverview()
    {
        $any_overview_active = $this->evaluation->isShowFbsOverviewBar() || $this->evaluation->isShowFbsOverviewSpider() ||
            $this->evaluation->isShowFbsOverviewLeftRight() || $this->evaluation->isShowFbsOverviewStatistics();
        return $this->evaluation->getShowFeedbacksOverview() && $any_overview_active;
    }

    protected function showAnyFeedback()
    {
        return $this->showAnyFeedbackCharts() || $this->evaluation->getShowBlockTitlesDuringFeedback() ||
            $this->evaluation->getShowBlockDescriptionsDuringFeedback() || $this->evaluation->getShowFeedbacks();
    }

    protected function initTemplate() : ilTemplate
    {
        $btn = ilButton::getInstance();
        $btn->setCaption($this->plugin->txt("print_pdf"), false);
        $btn->addCSSClass("printPDF");
        $btn->setOnClick("printFeedback()");
        $this->toolbar->addButtonInstance($btn);

        $tpl = $this->plugin->getTemplate('default/Feedback/tpl.feedback.html');
        $this->tpl->addCss("Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/templates/css/feedback.css");
        $this->tpl->addJavaScript("Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/templates/js/bar_spider_chart_toggle.js");
        $this->tpl->addJavaScript("Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/templates/js/print.js");
        return $tpl;
    }

    protected function getBlockLabel(ilSelfEvaluationQuestionBlock $block)
    {
        return $this->plugin->txt('block') . ' ' . ($block->getId() + 1);
    }

    protected function parseBlockFeedback(
        ilTemplate $tpl,
        ilSelfEvaluationQuestionBlock $block,
        Feedback $feedback,
        Dataset $data_set
    ) {
        $show_feedback_charts = $this->showAnyFeedbackCharts();

        if ($this->showAnyFeedback()) {
            $tpl->setCurrentBlock('feedback');
            $tpl->setVariable('FEEDBACK_ID', 'xsev_fb_id_' . $feedback->getId());

            if ($this->evaluation->getShowBlockTitlesDuringFeedback()) {
                $abbreviation = $block->getAbbreviation();
                $tpl->setVariable('BLOCK_TITLE', $block->getTitle());
                if ($abbreviation != '') {
                    $tpl->setCurrentBlock('block_abbreviation');
                    $tpl->setVariable('BLOCK_ABBREVIATION', $abbreviation);
                    $tpl->parseCurrentBlock();
                    $tpl->setCurrentBlock('feedback');
                }
            } else {
                $tpl->setVariable('BLOCK_TITLE', $this->getBlockLabel($block));
            }
            if ($this->evaluation->getShowBlockDescriptionsDuringFeedback()) {
                $tpl->setVariable('BLOCK_DESCRIPTION', $block->getDescription());
            }

            if ($show_feedback_charts) {
                $scale = Scale::_getInstanceByObjId($this->db, $this->evaluation->getId());
                $units = $scale->getUnitsAsArray();
                $max_cnt = max(array_keys($units));

                if ($this->evaluation->isShowFbsChartBar()) {
                    $bar_chart = $this->getFeedbackBarChart($data_set, $block->getId(), $units);
                    $tpl->setVariable('BAR_CHART', $bar_chart->getHTML());
                    $tpl->setVariable('SHOW_BAR_CHART', $this->plugin->txt('show_bar_chart'));
                }

                if ($this->evaluation->isShowFbsChartLeftRight()) {
                    $left_right_chart = $this->getFeedbackLeftRightChart($data_set, $block->getId(), $units);
                    $tpl->setVariable('LEFT_RIGHT_CHART', $left_right_chart->getHTML());
                    $tpl->setVariable('SHOW_LEFT_RIGHT_CHART', $this->plugin->txt('show_left_right_chart'));
                }

                if ($this->evaluation->isShowFbsChartSpider()) {
                    $spider_chart = $this->getFeedbackSpiderChart($data_set, $block->getId(), $max_cnt);
                    $tpl->setVariable('SPIDER_CHART', $spider_chart->getHTML());
                    $tpl->setVariable('SHOW_SPIDER_CHART', $this->plugin->txt('show_spider_chart'));
                }

            } else {
                $tpl->setVariable('VISIBILITY_GRAPH', "visibility: hidden");
            }

            if ($this->evaluation->getShowFeedbacks()) {
                $tpl->setVariable('FEEDBACK_TITLE', $feedback->getTitle());
                $tpl->setVariable('FEEDBACK_BODY', $feedback->getFeedbackText());

            }
            $tpl->parseCurrentBlock();
        }
    }

    protected function getFeedbackBarChart(Dataset $data_set, string $block_id, array $scale_units) : BarChart
    {
        $chart = new BarChart($block_id . "_feedback_bar_chart");
        $ticks = [];
        $x = 1;
        foreach ($data_set->getDataPerBlock($block_id) as $qst_id => $value) {
            $qst = new Question($this->db,$qst_id);
            $data = $chart->getDataInstance();

            $data->addPoint($x, $value);
            $ticks[$x] = $qst->getTitle() ? $qst->getTitle() : $this->plugin->txt
                ('question') . ' ' . $x;
            $x++;
            $chart->addData($data);

        }
        $scale_units = $this->setUnusedLegendLabels($scale_units);
        $chart->setTicks($ticks, $scale_units, true);

        return $chart;
    }

    protected function getFeedbackLeftRightChart(
        Dataset $data_set,
        string $block_id,
        array $scale_units
    ) : LeftRightChart {

        $chart = new LeftRightChart($block_id . "_feedback_left_right_chart");
        $data = $chart->getDataInstance();
        $ticks = [];
        $x = 99999;
        foreach ($data_set->getDataPerBlock($block_id) as $qst_id => $value) {
            $qst = new Question($this->db, $qst_id);
            $data->addPoint($value, $x);
            $ticks[$x] = $qst->getTitle() ? $qst->getTitle() : $this->plugin->txt
                ('question') . ' ' . $qst_id;
            $x--;

        }

        $scale_units = $this->setUnusedLegendLabels($scale_units);
        $chart->setTicks($scale_units, $ticks, true);
        $chart->addData($data);

        return $chart;
    }

    protected function getFeedbackSpiderChart(
        Dataset $data_set,
        string $block_id,
        int $max_level
    ) : SpiderChart {
        $chart = new SpiderChart($block_id . "_feedback_spider_chart");
        $data = $chart->getDataInstance();
        $leg_labels = [];
        $cnt = 0;
        foreach ($data_set->getDataPerBlock($block_id) as $qst_id => $value) {
            $qst = new Question($this->db, $qst_id);
            $data->addPoint($cnt, $value);
            $leg_labels[] = $qst->getTitle() ? $qst->getTitle() : $this->plugin->txt('question') . ' ' . ($cnt + 1);
            $cnt++;
        }
        $chart->setLegLabels($leg_labels); // This might be the questions
        $chart->setYAxisMax($max_level); // set the max number of net lines
        $chart->addData($data);

        return $chart;
    }

    protected function setUnusedLegendLabels($scale_unit)
    {
        $key = array_search('', $scale_unit);
        if ($key === false) {
            return $scale_unit;
        }

        $scale_unit[$key] = $key;
        return $scale_unit;
    }

    protected function getOverviewBarChart(array $block_data, $average) : BarChart
    {
        $chart = new BarChart('bar_overview');

        $x_axis = [];
        foreach ($block_data as $block_d) {
            $data = $chart->getDataInstance();
            $block = new ilSelfEvaluationQuestionBlock($block_d['block_id']);
            $data->addPoint($block->getPosition(), $block_d['percentage']);
            $x_axis[$block->getPosition()] = $block_d['label'];
            $chart->addData($data);
        }

        if ($this->evaluation->isOverviewBarShowLabelAsPercentage()) {
            // display y-axis in 10% steps
            $y_axis = [];
            for ($i = 0; $i <= 10; $i++) {
                $y_axis[$i * 10] = $i * 10 . '%';
            }
            $chart->setTicks($x_axis, $y_axis, true);
        } else {
            $scale = Scale::_getInstanceByObjId($this->db, $this->evaluation->getId());
            $units = $scale->getUnitsAsRelativeArray();
            $units = $this->setUnusedLegendLabels($units);
            $chart->setTicks($x_axis, $units, true);
        }

        $chart->setShowAverageLine(true);
        $chart->setAverage($average);

        return $chart;
    }

    protected function getOverviewLeftRightChart(array $block_data) : LeftRightChart
    {

        $chart = new LeftRightChart('left_right_overview');
        $data = $chart->getDataInstance();

        $y_axis = [];
        $y = 999999;

        foreach ($block_data as $block_d) {
            $block = new ilSelfEvaluationQuestionBlock($block_d['block_id']);
            $data->addPoint($block_d['percentage'], $y - $block->getPosition());
            $y_axis[$y - $block->getPosition()] = $block_d['label'];
            $chart->addData($data);
        }
        // display y-axis in 10% steps
        $x_axis = [];
        for ($i = 0; $i <= 10; $i++) {
            $x_axis[$i * 10] = $i * 10 . '%';
        }
        $chart->setTicks($x_axis, $y_axis, true);

        return $chart;
    }

    protected function getOverviewSpiderChart(array $block_data) : SpiderChart
    {
        $chart = new SpiderChart('spider_chart_overview');
        $data = $chart->getDataInstance();

        $cnt = 0;
        $leg_labels = [];
        foreach ($block_data as $block_d) {
            $leg_labels[] = $block_d['label'];
            $data->addPoint($cnt, $block_d['percentage'] / 10);
            $cnt++;
        }

        $chart->setLegLabels($leg_labels);
        $chart->setYAxisMax(10); // set the max number of net lines (10% steps)
        $chart->addData($data);

        return $chart;
    }
}