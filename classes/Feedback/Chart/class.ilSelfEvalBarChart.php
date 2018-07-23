<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Chart/classes/class.ilChartGrid.php";
include_once "Services/Chart/classes/class.ilChartDataBars.php";
include_once "trait.ilSelfEvalChartTrait.php";

/**
 * Class ilSelfEvalBarChart
 */
class ilSelfEvalBarChart extends ilChartGrid
{
    use ilSelfEvalChartTrait;

    const BAR_WIDTH = 0.5;

    /**
     * @var bool
     */
    protected $show_average_line = false;

    /**
     * @var int
     */
    protected $average = 0;

    public function __construct($a_id){
        parent::__construct($a_id);
        $this->setSize($this->getCanvasWidth(), $this->getCanvasHeight());
        $this->setColors($this->getChartColors());
        $this->setLegend($this->getLegend());
        $this->setAutoResize(true);

        $this->setYAxisToInteger(true);

    }

    public function getDataInstance($type = null)
    {
        $data = new ilChartDataBars();
        $data->setBarOptions(self::BAR_WIDTH, 'center');
        return $data;
    }

	/**
	 * @param stdClass $a_options
	 */
	public function parseGlobalOptions(stdClass $a_options)
	{
		parent::parseGlobalOptions($a_options);

		if($this->isShowAverageLine()){
            $a_options->{"grid"} = new stdClass();
            $a_options->{"grid"}->{"markings"} = [];
            $a_options->{"grid"}->{"markings"}[0] = new stdClass();
            $a_options->{"grid"}->{"markings"}[0]->{"yaxis"} = new stdClass();
            $a_options->{"grid"}->{"markings"}[0]->{"yaxis"}->from = $this->getAverage();
            $a_options->{"grid"}->{"markings"}[0]->{"yaxis"}->to = $this->getAverage();
            $a_options->{"grid"}->{"markings"}[0]->{"color"} = new stdClass();
            $a_options->{"grid"}->{"markings"}[0]->{"color"} = "#333333";
        }
	}

    /**
     * @return bool
     */
    public function isShowAverageLine()
    {
        return $this->show_average_line;
    }

    /**
     * @param bool $show_average_line
     */
    public function setShowAverageLine( $show_average_line)
    {
        $this->show_average_line = $show_average_line;
    }

    /**
     * @return int
     */
    public function getAverage(): int
    {
        return $this->average;
    }

    /**
     * @param int $average
     */
    public function setAverage(int $average)
    {
        $this->average = $average;
    }


}