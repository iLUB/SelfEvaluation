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
require_once('Services/Chart/classes/class.ilChartGrid.php');
require_once('Services/Chart/classes/class.ilChartSpider.php');

/**
 * Class ilSelfEvaluationFeedbackChartGUI
 *
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */
class ilSelfEvaluationFeedbackChartGUI {

	const BAR_WIDTH = 0.5;
	const WIDTH = "99%";
    const HEIGHT = "450px";
	/**
	 * @var ilSelfEvaluationPlugin
	 */
	protected $pl;


	public function __construct() {
        global $tpl;
        /**
         * @var $tpl    ilTemplate
         */
        $this->tpl = $tpl;

		$this->pl = new ilSelfEvaluationPlugin();
	}


	/**
	 * @param ilSelfEvaluationDataset $data_set
	 *
	 * @return string
	 */
	public function getPresentationOfFeedback(ilSelfEvaluationDataset $data_set) {
		/**
		 * @var $obj ilObjSelfEvaluation
		 */
		$factory = new ilObjectFactory();
		$obj = $factory->getInstanceByRefId($_GET['ref_id']);
		
		$tpl = $this->pl->getTemplate('default/Feedback/tpl.feedback.html');
        $this->tpl->addCss("Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/templates/css/feedback.css");
		$this->tpl->addJavaScript("Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/templates/js/bar_spider_chart_toggle.js");

        $color_id = 0;
		$percentages = $data_set->getPercentagePerBlock();
		$blocks = array();
		foreach ($data_set->getFeedbacksPerBlock() as $block_id => $fb) {
            $block = new ilSelfEvaluationQuestionBlock($block_id);

            if($obj->getShowFeedbacksCharts() || $obj->getShowBlockTitlesDuringFeedback() || $obj->getShowBlockDescriptionsDuringFeedback() || $obj->getShowFeedbacks()){
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
                    $block_label = $this->pl->txt('block') . ' ' . ($color_id + 1);
                    $tpl->setVariable('BLOCK_TITLE', $block_label);
                }
                if ($obj->getShowBlockDescriptionsDuringFeedback()) {
                    $tpl->setVariable('BLOCK_DESCRIPTION', $block->getDescription());
                }

                if ($obj->getShowFeedbacksCharts()) {
                    $scale = ilSelfEvaluationScale::_getInstanceByRefId($obj->getId());
                    $units = $scale->getUnitsAsArray();
                    $max_cnt = max(array_keys($units));

                    $bar_chart = $this->getFeedbackBlockBarChart($data_set, $block_id, $color_id, $units);
                    $tpl->setVariable('BAR_CHART', $bar_chart->getHTML());
                    $tpl->setVariable('SHOW_BAR_CHART', $this->pl->txt('show_bar_chart'));

	                $left_right_chart = $this->getFeedbackLeftRightChart($data_set,
			                $block_id, $color_id, $units);
	                $tpl->setVariable('LEFT_RIGHT_CHART', $left_right_chart->getHTML());
	                $tpl->setVariable('SHOW_LEFT_RIGHT_CHART', $this->pl->txt
	                ('show_left_right_chart'));

                    $spider_chart = $this->getFeedbackBlockSpiderChart($data_set, $block_id, $color_id, $max_cnt);
                    $tpl->setVariable('SPIDER_CHART', $spider_chart->getHTML());
                    $tpl->setVariable('SHOW_SPIDER_CHART', $this->pl->txt('show_spider_chart'));
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
			$color_id ++;
		}
		if (count($data_set->getFeedbacksPerBlock()) > 0 AND $obj->getShowFeedbacksOverview()) {
			$tpl->setVariable('OVERVIEW_BAR_CHART', $this->getOverviewBlockChart($blocks)->getHTML());
			$tpl->setVariable('OVERVIEW_LEFT_RIGHT_CHART', $this->getOverviewLeftRightChart($blocks)->getHTML());
			$tpl->setVariable('OVERVIEW_SPIDER_CHART', $this->getOverviewSpiderChart($blocks)->getHTML());
			$tpl->setVariable('SHOW_SPIDER_CHART', $this->pl->txt('show_spider_chart'));
			$tpl->setVariable('SHOW_BAR_CHART', $this->pl->txt('show_bar_chart'));
			$tpl->setVariable('SHOW_LEFT_RIGHT_CHART', $this->pl->txt('show_left_right_chart'));

		}
		if(!$obj->getShowFeedbacksOverview()) {
			$tpl->setVariable('VISIBILITY_OVERVIEW', "hidden");
		}

		return $tpl->get();
	}


	/**
	 * Creates a chart, adds a default legend and sets the colors
	 *
	 * @param string $chart_id  unique identifier for a feedback chart
	 * @param array  $colors    array of used color values
	 *
	 * @return ilChartGrid
	 */
	protected function initBarChart($chart_id, $colors) {
		/** @var ilChartGrid $chart */
		$chart = $this->initChart(ilChart::TYPE_GRID, $chart_id . '_blk', $colors);
		$chart->setYAxisToInteger(true);

		return $chart;
	}


	/**
	 * @param string $chart_id  unique identifier for a feedback chart
	 * @param array  $colors    array of used color values
	 *
	 * @return ilChartSpider
	 */
	protected function initSpiderChart($chart_id, $colors) {
		return $this->initChart(ilChart::TYPE_SPIDER, $chart_id . '_sdr', $colors);
	}

	/**
	 * @param string $chart_id  unique identifier for a feedback chart
	 * @param array  $colors    array of used color values
	 *
	 * @return ilChartSpider
	 */
	protected function initLeftRightChart($chart_id, $colors) {
		/** @var ilChartGrid $chart */
		$chart = $this->initChart(ilChart::TYPE_GRID, $chart_id . '_lr', $colors);
		$chart->setYAxisToInteger(true);

		return $chart;	}

	/**
	 * @param int    $chart_type    the chart type (see ilChart constants)
	 * @param string $chart_id      the unique identifier for this chart
	 * @param array  $colors        the array of used color values
	 *
	 * @return ilChart
	 */
	protected function initChart($chart_type, $chart_id, $colors) {
		$chart = ilChart::getInstanceByType($chart_type, $chart_id);
		$chart->setSize(self::WIDTH, self::HEIGHT);
		$legend = new ilChartLegend();
		$legend->setBackground($colors[0]);
		$chart->setColors($colors);
		$chart->setLegend($legend);
        $chart->setAutoResize(true);

		return $chart;
	}


	/**
	 * @param ilSelfEvaluationDataset $data_set
	 * @param string                  $block_id unique identifier for a feedback block
	 * @param int                     $color_id index of the used color
	 * @param array                   $scale_units
	 *
	 * @return ilChart
	 */
	protected function getFeedbackBlockBarChart(ilSelfEvaluationDataset $data_set, $block_id, $color_id, $scale_units) {
		$colors = $this->getChartColors();
		$chart = self::initBarChart($block_id, array($colors[$color_id]));
		$data = $chart->getDataInstance(ilChartGrid::DATA_BARS);
		//$chart->setXAxisToInteger(false);
		$data->setBarOptions(self::BAR_WIDTH, 'center');
		$ticks = array();
		$x = 1;
		foreach ($data_set->getDataPerBlock($block_id) as $qst_id => $value) {
			$qst = new ilSelfEvaluationQuestion($qst_id);
			$data->addPoint($x, $value);
			$ticks[$x] = $qst->getTitle() ? $qst->getTitle() : $this->pl->txt('question') . ' ' . $x;
			$x ++;
		}
		self::setUnusedLegendLabels($scale_units);
		$chart->setTicks($ticks, $scale_units, true);
		$chart->addData($data);

		return $chart;
	}

	/**
	 * @param ilSelfEvaluationDataset $data_set
	 * @param string                  $block_id unique identifier for a feedback block
	 * @param int                     $color_id index of the used color
	 * @param array                   $scale_units
	 *
	 * @return ilChart
	 */
	protected function getFeedbackLeftRightChart(ilSelfEvaluationDataset $data_set,
	                                          $block_id, $color_id, $scale_units) {
		$colors = $this->getChartColors();
		$chart = self::initLeftRightChart($block_id, array($colors[$color_id]));
		$data = $chart->getDataInstance(ilChartGrid::DATA_BARS);
		//$chart->setXAxisToInteger(false);
		$data->setBarOptions(self::BAR_WIDTH, 'center');
		$ticks = array();
		$x = 1;
		foreach ($data_set->getDataPerBlock($block_id) as $qst_id => $value) {
			$qst = new ilSelfEvaluationQuestion($qst_id);
			$data->addPoint($x, $value);
			$ticks[$x] = $qst->getTitle() ? $qst->getTitle() : $this->pl->txt('question') . ' ' . $x;
			$x ++;
		}
		self::setUnusedLegendLabels($scale_units);
		$chart->setTicks($ticks, $scale_units, true);
		$chart->addData($data);

		return $chart;
	}

	/**
	 * @param array $scale_unit
	 */
	protected function setUnusedLegendLabels(&$scale_unit) {
		$key = array_search('', $scale_unit);
		if ($key === FALSE) {
			return;
		}

		$scale_unit[$key] = $key;
	}


	/**
	 * @param ilSelfEvaluationDataset $data_set
	 * @param string                  $block_id     unique identifier for a feedback block
	 * @param int                     $color_id     index of the used color
	 * @param int                     $max_level    the number of displayed levels in the spider chart
	 *
	 * @return ilChart
	 */
	protected function getFeedbackBlockSpiderChart(ilSelfEvaluationDataset $data_set, $block_id, $color_id, $max_level) {
		$colors = $this->getChartColors();
		$chart = $this->initSpiderChart($block_id, array($colors[$color_id]));
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
	 * @param array $block_data
	 *
	 * @return ilChart
	 */
	protected function getOverviewBlockChart(array $block_data) {
		$chart = $this->initBarChart('fb_overview', $this->getChartColors());
		$chart->setSize(self::WIDTH, self::HEIGHT);

		$x_axis = array();
		foreach ($block_data as $block_d) {
			$data = $chart->getDataInstance(ilChartGrid::DATA_BARS);
			$data->setBarOptions(self::BAR_WIDTH, 'center');
			$block = new ilSelfEvaluationQuestionBlock($block_d['block_id']);
			$data->addPoint($block->getPosition(), $block_d['percentage']);
			$x_axis[$block->getPosition()] = $block_d['label'];
			$chart->addData($data);
		}
		// display y-axis in 10% steps
		$y_axis = array();
		for ($i = 0; $i <= 10; $i++) {
			$y_axis[$i * 10] = $i * 10 . '%';
		}
		$chart->setTicks($x_axis, $y_axis, true);

		return $chart;
	}

	/**
	 * @param array $block_data
	 *
	 * @return ilChart
	 */
	protected function getOverviewLeftRightChart(array $block_data) {
		$chart = $this->initLeftRightChart('fb_overview', $this->getChartColors());
		$chart->setSize(self::WIDTH, self::HEIGHT);

		$x_axis = array();
		foreach ($block_data as $block_d) {
			$data = $chart->getDataInstance(ilChartGrid::DATA_BARS);
			$data->setBarOptions(self::BAR_WIDTH, 'center');
			$block = new ilSelfEvaluationQuestionBlock($block_d['block_id']);
			$data->addPoint($block->getPosition(), $block_d['percentage']);
			$x_axis[$block->getPosition()] = $block_d['label'];
			$chart->addData($data);
		}
		// display y-axis in 10% steps
		$y_axis = array();
		for ($i = 0; $i <= 10; $i++) {
			$y_axis[$i * 10] = $i * 10 . '%';
		}
		$chart->setTicks($x_axis, $y_axis, true);

		return $chart;
	}

	/**
	 * @param array $block_data
	 *
	 * @return ilChart
	 */
	protected function getOverviewSpiderChart(array $block_data) {
		/** @var ilChartSpider $chart $chart */
		$colors = $this->getChartColors();
		$chart = $this->initSpiderChart('fb_overview', array($colors[count($colors)-1]));
        $chart->setSize(self::WIDTH, self::HEIGHT);
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


	/**
	 * @return array containing color codes
	 */
	protected function getChartColors() {
		return array(
			'#00CCFF',
			'#00CC99',
			'#9999FF',
			'#CC66FF',
			'#FF99FF',
			'#FF9933',
			'#CCCC33',
			'#CC6666',
			'#669900',
			'#666600',
			'#333399',
			'#0066CC',
		);
	}
}