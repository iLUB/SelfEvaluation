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
     * @var bool
     */
    protected $show_varianz = false;

    /**
     * @var array
     */
    protected $standardabweichung_data = [];

    /**
     * @var array
     */
    protected $values_for_standardabweichung = [];

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
        $a_options->{"grid"} = new stdClass();
        $a_options->{"grid"}->{"markings"} = [];

        if($this->isShowAverageLine()){
            $a_options->{"grid"}->{"markings"}[0] = new stdClass();
            $a_options->{"grid"}->{"markings"}[0]->{"yaxis"} = new stdClass();
            $a_options->{"grid"}->{"markings"}[0]->{"yaxis"}->from = $this->getAverage();
            $a_options->{"grid"}->{"markings"}[0]->{"yaxis"}->to = $this->getAverage();
            $a_options->{"grid"}->{"markings"}[0]->{"color"} = new stdClass();
            $a_options->{"grid"}->{"markings"}[0]->{"color"} = "#333333";
        }

        if($this->isShowVarianz()){
            $y_values = $this->getValuesForStandardabweichung();
            $x = 1;
            foreach ($this->getStandardabweichungData() as $key => $sd_data){
                $a_options->{"grid"}->{"markings"}[$x] = new stdClass();
                $a_options->{"grid"}->{"markings"}[$x]->{"yaxis"} = new stdClass();
                $a_options->{"grid"}->{"markings"}[$x]->{"yaxis"}->from = $y_values[$key]-$sd_data/2;
                $a_options->{"grid"}->{"markings"}[$x]->{"yaxis"}->to = $y_values[$key]+$sd_data/2;

                $a_options->{"grid"}->{"markings"}[$x]->{"xaxis"} = new stdClass();
                $a_options->{"grid"}->{"markings"}[$x]->{"xaxis"}->from =$x+1;
                $a_options->{"grid"}->{"markings"}[$x]->{"xaxis"}->to = $x+1;

                $a_options->{"grid"}->{"markings"}[$x]->{"color"} = new stdClass();
                $a_options->{"grid"}->{"markings"}[$x]->{"color"} = "#333333";
                $x++;
            }
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

    /**
     * @return bool
     */
    public function isShowVarianz()
    {
        return $this->show_varianz;
    }

    /**
     * @param bool $show_varianz
     */
    public function setShowVarianz( $show_varianz)
    {
        $this->show_varianz = $show_varianz;
    }

    /**
     * @return array
     */
    public function getVarianzData(): array
    {
        return $this->varianz_data;
    }

    /**
     * @return array
     */
    public function getStandardabweichungData()
    {
        return $this->standardabweichung_data;
    }

    /**
     * @param array $standardabweichung_data
     */
    public function setStandardabweichungData( $standardabweichung_data)
    {
        $this->standardabweichung_data = $standardabweichung_data;
    }

    /**
     * @return array
     */
    public function getValuesForStandardabweichung()
    {
        return $this->values_for_standardabweichung;
    }

    /**
     * @param array $values_for_standardabweichung
     */
    public function setValuesForStandardabweichung( $values_for_standardabweichung)
    {
        $this->values_for_standardabweichung = $values_for_standardabweichung;
    }
}