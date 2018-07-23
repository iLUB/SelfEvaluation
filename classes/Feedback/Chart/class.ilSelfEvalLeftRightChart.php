<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Chart/classes/class.ilChartGrid.php";
include_once "Services/Chart/classes/class.ilChartDataBars.php";
include_once "trait.ilSelfEvalChartTrait.php";

/**
 * Class ilSelfEvalChart
 */
class ilSelfEvalLeftRightChart extends ilChartGrid
{
    use ilSelfEvalChartTrait;

    public function __construct($a_id){
        parent::__construct($a_id);
        $this->setSize($this->getCanvasWidth(), $this->getCanvasHeight());
        $this->setColors($this->getChartColors());
        $this->setLegend($this->getLegend());
        $this->setAutoResize(true);

        $this->setXAxisToInteger(false);
        $this->setYAxisToInteger(true);
    }

    public function getDataInstance($type = null)
    {
        return new ilChartDataLines();
    }

	/**
	 * @param stdClass $a_options
	 */
	public function parseGlobalOptions(stdClass $a_options)
	{
		parent::parseGlobalOptions($a_options);
		$x_tick_key = array_keys($this->getTicks()["x"]);
		$y_tick_key = array_keys($this->getTicks()["y"]);


		$a_options->{"yaxis"}->labelWidth = 0;

		$a_options->{"yaxis"}->min = min($y_tick_key);
		$a_options->{"yaxis"}->max = max($y_tick_key);

		$a_options->{"xaxis"}->min = min($x_tick_key);
		$a_options->{"xaxis"}->max = max($x_tick_key);

		$a_options->{"grid"} = new stdClass();
		$a_options->{"grid"}->{"markings"} = [];
		$a_options->{"grid"}->{"markings"}[0] = new stdClass();
		$a_options->{"grid"}->{"markings"}[0]->{"xaxis"} = new stdClass();
		$middle = (min($x_tick_key)+max($x_tick_key))/2;
		$a_options->{"grid"}->{"markings"}[0]->{"xaxis"}->from = $middle;
		$a_options->{"grid"}->{"markings"}[0]->{"xaxis"}->to = $middle;
		$a_options->{"grid"}->{"markings"}[0]->{"color"} = new stdClass();
		$a_options->{"grid"}->{"markings"}[0]->{"color"} = "#333333";

	}
}