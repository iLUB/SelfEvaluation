<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2014 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/
require_once('Services/Chart/classes/class.ilChartSpider.php');
require_once("Chart/class.ilSelfEvalBarChart.php");
require_once("Chart/class.ilSelfEvalLeftRightChart.php");
require_once("Chart/class.ilSelfEvalSpiderChart.php");

/**
 * Class ilSelfEvaluationFeedbackChartGUI
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */
class ilSelfEvaluationFeedbackChartGUI
{
    /**
     * @var ilSelfEvaluationPlugin
     */
    protected $pl;

    public function __construct(ilGlobalPageTemplate $tpl)
    {
        $this->tpl = $tpl;

        $this->pl = new ilSelfEvaluationPlugin();
    }

    /**
     * @param ilSelfEvaluationDataset $data_set
     * @return string
     */
    public function getPresentationOfFeedback(ilSelfEvaluationDataset $data_set)
    {
        global $DIC;

        $btn = ilButton::getInstance();
        $btn->setCaption($this->pl->txt("print_pdf"), false);
        $btn->addCSSClass("printPDF");
        $btn->setOnClick("printFeedback()");
        $DIC->toolbar()->addButtonInstance($btn);

        /**
         * @var $obj ilObjSelfEvaluation
         */
        $factory = new ilObjectFactory();
        $obj = $factory->getInstanceByRefId($_GET['ref_id']);

        $tpl = $this->pl->getTemplate('default/Feedback/tpl.feedback.html');
        $this->tpl->addCss("Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/templates/css/feedback.css");
        $this->tpl->addJavaScript("Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/templates/js/bar_spider_chart_toggle.js");
        $this->tpl->addJavaScript("Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/templates/js/print.js");

        $percentages = $data_set->getPercentagePerBlock();
        $blocks = array();
        foreach ($data_set->getFeedbacksPerBlock() as $block_id => $fb) {
            $block = new ilSelfEvaluationQuestionBlock($block_id);

            $show_feedback_charts = $obj->getShowFeedbacksCharts() &&
                ($obj->isShowFbsChartBar() || $obj->isShowFbsChartSpider() ||
                    $obj->isShowFbsChartLeftRight());
            $show_feedback_overview = $obj->getShowFeedbacksOverview() &&
                ($obj->isShowFbsOverviewBar() || $obj->isShowFbsOverviewSpider() ||
                    $obj->isShowFbsOverviewLeftRight() || $obj->isShowFbsOverviewStatistics()
                    || $obj->getShowFeedbacksOverview());

            if ($show_feedback_charts || $obj->getShowBlockTitlesDuringFeedback() ||
                $obj->getShowBlockDescriptionsDuringFeedback() || $obj->getShowFeedbacks()) {
                $tpl->setCurrentBlock('feedback');

                $tpl->setVariable('FEEDBACK_ID', 'xsev_fb_id_' . $fb->getId());

                if ($obj->getShowBlockTitlesDuringFeedback()) {
                    $abbreviation = $block->getAbbreviation();
                    $tpl->setVariable('BLOCK_TITLE', $block->getTitle());
                    if ($abbreviation != '') {
                        $tpl->setCurrentBlock('block_abbreviation');
                        $tpl->setVariable('BLOCK_ABBREVIATION', $abbreviation);
                        $tpl->parseCurrentBlock();
                        $tpl->setCurrentBlock('feedback');
                    }
                } else {
                    // Display a generic title when titles are not allowed but a mapping from overview to block is needed
                    $block_label = $this->pl->txt('block') . ' ' . ($block_id + 1);
                    $tpl->setVariable('BLOCK_TITLE', $block_label);
                }
                if ($obj->getShowBlockDescriptionsDuringFeedback()) {
                    $tpl->setVariable('BLOCK_DESCRIPTION', $block->getDescription());
                }

                if ($show_feedback_charts) {
                    $scale = ilSelfEvaluationScale::_getInstanceByObjId($obj->getId());
                    $units = $scale->getUnitsAsArray();
                    $max_cnt = max(array_keys($units));

                    if ($obj->isShowFbsChartBar()) {
                        $bar_chart = $this->getFeedbackBarChart($data_set, $block_id, $units);
                        $tpl->setVariable('BAR_CHART', $bar_chart->getHTML());
                        $tpl->setVariable('SHOW_BAR_CHART', $this->pl->txt('show_bar_chart'));
                    }

                    if ($obj->isShowFbsChartLeftRight()) {
                        $left_right_chart = $this->getFeedbackLeftRightChart($data_set, $block_id, $units);
                        $tpl->setVariable('LEFT_RIGHT_CHART', $left_right_chart->getHTML());
                        $tpl->setVariable('SHOW_LEFT_RIGHT_CHART', $this->pl->txt('show_left_right_chart'));
                    }

                    if ($obj->isShowFbsChartSpider()) {
                        $spider_chart = $this->getFeedbackSpiderChart($data_set, $block_id, $max_cnt);
                        $tpl->setVariable('SPIDER_CHART', $spider_chart->getHTML());
                        $tpl->setVariable('SHOW_SPIDER_CHART', $this->pl->txt('show_spider_chart'));
                    }

                } else {
                    $tpl->setVariable('VISIBILITY_GRAPH', "visibility: hidden");
                }

                $block_label = $abbreviation == '' ? $block->getTitle() : $abbreviation;
                if ($obj->getShowFeedbacks()) {
                    // Template
                    $tpl->setVariable('FEEDBACK_TITLE', $fb->getTitle());
                    $tpl->setVariable('FEEDBACK_BODY', $fb->getFeedbackText());

                }
                $tpl->parseCurrentBlock();
            }

            $blocks[] = array(
                'block_id' => $block->getId(),
                'percentage' => $percentages[$block->getId()],
                'label' => $block_label
            );
        }
        if (count($data_set->getFeedbacksPerBlock()) > 0 AND $show_feedback_overview) {
            $tpl->setCurrentBlock('overview');

            $tpl->setVariable('BLOCK_OVERVIEW_TITLE', $this->pl->txt('block_overview_title'));

            $mean = $data_set->getOverallPercentage();
            $min = $data_set->getMinPercentageBlock();
            $max = $data_set->getMaxPercentageBlock();
            $varianz = $data_set->getOverallVarianz();
            $standardabweichung = $data_set->getOverallStandardabweichung();
            $sd_per_block = $data_set->getStandardabweichungPerBlock();
            $scale_max = $data_set->getHighestValueFromScale();

            $statistics_median = $this->pl->txt("overview_statistics_median") . " " . round($scale_max * $mean / 100,
                    2);//." (".$mean."%)";
            $statistics_max = $this->pl->txt("overview_statistics_max") . " " . $max['block']->getTitle() . ": " . round($scale_max * $max['percentage'] / 100,
                    2);// ." (".$max['percentage']."%)";
            $statistics_min = $this->pl->txt("overview_statistics_min") . " " . $min['block']->getTitle() . ": " . round($scale_max * $min['percentage'] / 100,
                    2);// ." (".$min['percentage']."%)";
            $statistics_varianz = $this->pl->txt("overview_statistics_varianz") . ": " . $varianz;
            $statistics_sd_per_block = $this->pl->txt("overview_statistics_standardabweichung_per_plock") . ": ";
            foreach ($sd_per_block as $key => $sd) {
                $statistics_sd_per_block .= $data_set->getBlockById($key)->getTitle() . ": " . $sd . "; ";
            }
            $statistics_standardabweichung = $this->pl->txt("overview_statistics_standardabweichung") . ": " . $standardabweichung;

            if ($obj->isShowFbsOverviewStatistics()) {
                $tpl->setVariable('OVERVIEW_STATISTICS_TITLE', $this->pl->txt("overview_statistics_title"));

                $tpl->setVariable('OVERVIEW_STATISTICS_MEDIAN', $statistics_median);
                $tpl->setVariable('OVERVIEW_STATISTICS_MAX', $statistics_max);
                $tpl->setVariable('OVERVIEW_STATISTICS_MIN', $statistics_min);
                //$tpl->setVariable('OVERVIEW_VARIANZ', $statistics_varianz);
                //$tpl->setVariable('OVERVIEW_STANDARDABWEICHUNG', $statistics_standardabweichung);
                //$tpl->setVariable('OVERVIEW_STANDARDABWEICHUNG_PER_BLOCK', $statistics_sd_per_block);
            }

            if ($obj->isShowFbsOverviewBar()) {
                $tpl->setVariable('SHOW_BAR_CHART', $this->pl->txt('show_bar_chart'));
                $chart = $this->getOverviewBarChart($blocks, $data_set->getOverallPercentage());
                if ($obj->isShowFbsOverviewStatistics()) {
                    $chart->setShowVarianz(false);
                    $chart->setStandardabweichungData($sd_per_block);
                    $chart->setValuesForStandardabweichung($data_set->getPercentagePerBlock());
                }
                $tpl->setVariable('OVERVIEW_BAR_CHART', $chart->getHTML());
            }
            if ($obj->isShowFbsOverviewSpider()) {
                $tpl->setVariable('OVERVIEW_SPIDER_CHART', $this->getOverviewSpiderChart($blocks)->getHTML());
                $tpl->setVariable('SHOW_SPIDER_CHART', $this->pl->txt('show_spider_chart'));
            }
            if ($obj->isShowFbsOverviewLeftRight()) {
                $tpl->setVariable('OVERVIEW_LEFT_RIGHT_CHART', $this->getOverviewLeftRightChart($blocks)->getHTML());
                $tpl->setVariable('SHOW_LEFT_RIGHT_CHART', $this->pl->txt('show_left_right_chart'));
            }

            if ($obj->isShowFbsOverviewText()) {
                $feedback = ilSelfEvaluationFeedback::_getFeedbackForPercentage($obj->getId(), $mean);;
                if ($feedback) {
                    $tpl->setVariable('FEEDBACK_OVERVIEW_TITLE', $feedback->getTitle());
                    $tpl->setVariable('FEEDBACK_OVERVIEW_BODY', $feedback->getFeedbackText());
                }
            }
            $tpl->parseCurrentBlock();

        }

        return $tpl->get();
    }

    /**
     * @param ilSelfEvaluationDataset $data_set
     * @param string                  $block_id unique identifier for a feedback block
     * @param array                   $scale_units
     * @return ilChart
     */
    protected function getFeedbackBarChart(ilSelfEvaluationDataset $data_set, $block_id, $scale_units)
    {
        $chart = new ilSelfEvalBarChart($block_id . "_feedback_bar_chart");
        $ticks = array();
        $x = 1;
        foreach ($data_set->getDataPerBlock($block_id) as $qst_id => $value) {
            $qst = new ilSelfEvaluationQuestion($qst_id);
            $data = $chart->getDataInstance();

            $data->addPoint($x, $value);
            $ticks[$x] = $qst->getTitle() ? $qst->getTitle() : $this->pl->txt
                ('question') . ' ' . $x;
            $x++;
            $chart->addData($data);

        }
        $scale_units = $this->setUnusedLegendLabels($scale_units);
        $chart->setTicks($ticks, $scale_units, true);

        return $chart;
    }

    /**
     * @param ilSelfEvaluationDataset $data_set
     * @param string                  $block_id unique identifier for a feedback block
     * @param array                   $scale_units
     * @return ilChart
     */
    protected function getFeedbackLeftRightChart(
        ilSelfEvaluationDataset $data_set,
        $block_id,
        $scale_units
    ) {

        $chart = new ilSelfEvalLeftRightChart($block_id . "_feedback_left_right_chart");
        $data = $chart->getDataInstance();
        $ticks = array();
        $x = 99999;
        foreach ($data_set->getDataPerBlock($block_id) as $qst_id => $value) {
            $qst = new ilSelfEvaluationQuestion($qst_id);
            $data->addPoint($value, $x);
            $ticks[$x] = $qst->getTitle() ? $qst->getTitle() : $this->pl->txt
                ('question') . ' ' . $qst_id;
            $x--;

        }

        $scale_units = $this->setUnusedLegendLabels($scale_units);
        $chart->setTicks($scale_units, $ticks, true);
        $chart->addData($data);

        return $chart;
    }

    /**
     * @param ilSelfEvaluationDataset $data_set
     * @param string                  $block_id  unique identifier for a feedback block
     * @param int                     $max_level the number of displayed levels in the spider chart
     * @return ilChart
     */
    protected function getFeedbackSpiderChart(ilSelfEvaluationDataset $data_set, $block_id, $max_level)
    {
        $chart = new ilSelfEvalSpiderChart($block_id . "_feedback_spider_chart");
        $data = $chart->getDataInstance();
        $leg_labels = array();
        $cnt = 0;
        foreach ($data_set->getDataPerBlock($block_id) as $qst_id => $value) {
            $qst = new ilSelfEvaluationQuestion($qst_id);
            $data->addPoint($cnt, $value);
            $leg_labels[] = $qst->getTitle() ? $qst->getTitle() : $this->pl->txt('question') . ' ' . ($cnt + 1);
            $cnt++;
        }
        $chart->setLegLabels($leg_labels); // This might be the questions
        $chart->setYAxisMax($max_level); // set the max number of net lines
        $chart->addData($data);

        return $chart;
    }

    /**
     * @param $scale_unit
     * @return mixed
     */
    protected function setUnusedLegendLabels($scale_unit)
    {
        $key = array_search('', $scale_unit);
        if ($key === false) {
            return $scale_unit;
        }

        $scale_unit[$key] = $key;
        return $scale_unit;
    }

    /**
     * @param array $block_data
     * @param       $average
     * @return ilSelfEvalBarChart
     */
    protected function getOverviewBarChart(array $block_data, $average)
    {
        $chart = new ilSelfEvalBarChart('bar_overview');
        /**
         * @var $obj ilObjSelfEvaluation
         */
        $factory = new ilObjectFactory();
        $obj = $factory->getInstanceByRefId($_GET['ref_id']);

        $x_axis = array();
        foreach ($block_data as $block_d) {
            $data = $chart->getDataInstance();
            $block = new ilSelfEvaluationQuestionBlock($block_d['block_id']);
            $data->addPoint($block->getPosition(), $block_d['percentage']);
            $x_axis[$block->getPosition()] = $block_d['label'];
            $chart->addData($data);
        }

        if ($obj->isOverviewBarShowLabelAsPercentage()) {
            // display y-axis in 10% steps
            $y_axis = array();
            for ($i = 0; $i <= 10; $i++) {
                $y_axis[$i * 10] = $i * 10 . '%';
            }
            $chart->setTicks($x_axis, $y_axis, true);
        } else {
            $scale = ilSelfEvaluationScale::_getInstanceByObjId($obj->getId());
            $units = $scale->getUnitsAsRelativeArray();
            $units = $this->setUnusedLegendLabels($units);
            $chart->setTicks($x_axis, $units, true);
        }

        $chart->setShowAverageLine(true);
        $chart->setAverage($average);

        return $chart;
    }

    /**
     * @param array $block_data
     * @return ilChart
     */
    protected function getOverviewLeftRightChart(array $block_data)
    {

        $chart = new ilSelfEvalLeftRightChart('left_right_overview');
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
        $x_axis = array();
        for ($i = 0; $i <= 10; $i++) {
            $x_axis[$i * 10] = $i * 10 . '%';
        }
        $chart->setTicks($x_axis, $y_axis, true);

        return $chart;
    }

    /**
     * @param array $block_data
     * @return ilChart
     */
    protected function getOverviewSpiderChart(array $block_data)
    {
        $chart = new ilSelfEvalSpiderChart('spider_chart_overview');
        $data = $chart->getDataInstance();

        $cnt = 0;
        $leg_labels = array();
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