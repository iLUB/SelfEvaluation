<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Feedback/Chart/classes/class.ilChart.php";

/**
 * Generator for grid-based charts
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesChart
 */
class ilChartGrid50 extends ilChart50
{
	protected $ticks; // [array]
	protected $integer_axis; // [array]
	protected $min_axis; // [array]
	protected $max_axis; // [array]

	const DATA_LINES = 1;
	const DATA_BARS = 2;
	const DATA_POINTS = 3;
	
	protected function __construct($a_id)
	{
		parent::__construct($a_id);
		
		$this->setXAxisToInteger(false);
		$this->setYAxisToInteger(false);
	}
	
	public function getDataInstance($a_type = null)
	{
		switch($a_type)
		{				
			case self::DATA_BARS:
				include_once "Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Feedback/Chart/classes/class.ilChartDataBars.php";
				return new ilChartDataBars50();
				
			case self::DATA_POINTS:
				include_once "Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Feedback/Chart/classes/class.ilChartDataPoints.php";
				return new ilChartDataPoints50();
			
			default:
			case self::DATA_LINES:
				include_once "Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Feedback/Chart/classes/class.ilChartDataLines.php";
				return new ilChartDataLines50();
		}						
	}
	
	protected function isValidDataType(ilChartData50 $a_series)
	{
		if($a_series instanceof ilChartDataLines50
			|| $a_series instanceof ilChartDataBars50
			|| $a_series instanceof ilChartDataPoints50)
		{
			return true;
		}
		return false;
	}
		
	/**
	 * Set ticks
	 *
	 * @param int|array $a_x
	 * @param int|array $a_y
	 * @param bool $a_labeled
	 */
	public function setTicks($a_x, $a_y, $a_labeled = false)
	{
		$this->ticks = array("x" => $a_x, "y" => $a_y, "labeled" => (bool)$a_labeled);
	}

	/**
	 * Get ticks
	 *
	 * @return array (x, y)
	 */
	public function getTicks()
	{
		return $this->ticks;
	}
	
	/**
	 * Restrict y-axis to integer values
	 * 
	 * @param bool $a_status
	 */
	public function setYAxisToInteger($a_status)
	{
		$this->integer_axis["y"] = (bool)$a_status;
	}
	
	/**
	 * Restrict x-axis to integer values
	 * 
	 * @param bool $a_status
	 */
	public function setXAxisToInteger($a_status)
	{
		$this->integer_axis["x"] = (bool)$a_status;
	}

	/**
	 * Set the minimum x-axis value
	 * @param int $min_value
	 */
	public function setXAxisMin($min_value)
	{
		$this->min_axis["x"] = (int)$min_value;
	}

	/**
	 * Set the minimum y-axis value
	 * @param int $min_value
	 */
	public function setYAxisMin($min_value)
	{
		$this->min_axis["y"] = (int)$min_value;
	}

	/**
	 * Set the maximum x-axis value
	 * @param $max_value
	 */
	public function setXAxisMax($max_value)
	{
		$this->max_axis["x"] = (int)$max_value;
	}

	/**
	 * Set the maximum y-axis value
	 * @param $max_value
	 */
	public function setYAxisMax($max_value)
	{
		$this->max_axis["y"] = (int)$max_value;
	}

	public function parseGlobalOptions(stdClass $a_options)
	{	
		// axis/ticks
		$ticks = $this->getTicks();
		if($ticks)
		{			
			$labeled = (bool)$ticks["labeled"];
			unset($ticks["labeled"]);		
			foreach($ticks as $axis => $def)
			{				
				if(is_numeric($def) || is_array($def))
				{
					$a_options->{$axis."axis"} = new stdClass();
				}
				if(is_numeric($def))
				{					
					$a_options->{$axis."axis"}->ticks = $def;
				}
				else if(is_array($def))
				{
					$a_options->{$axis."axis"}->ticks = array();
					foreach($def as $idx => $value)
					{
						if($labeled)
						{
							$a_options->{$axis."axis"}->ticks[] = array($idx, $value);
						}
						else
						{
							$a_options->{$axis."axis"}->ticks[] = $value;
						}
					}
				}
			}
		}
		
		// optional: remove decimals
	    if($this->integer_axis["x"] && $this->checkXAxis($a_options))
		{
			$a_options->{"xaxis"}->tickDecimals = 0;
		}
		if($this->integer_axis["y"] && $this->checkYAxis($a_options))
		{
			$a_options->{"yaxis"}->tickDecimals = 0;
		}
		$this->parseMinMaxOptions($a_options);
	}

	protected function checkXAxis(&$a_options)
	{
		if(!isset($a_options->xaxis))
		{
			$a_options->{"xaxis"} = new stdClass();
		}

		return true;
	}

	protected function checkYAxis(&$a_options)
	{
		if(!isset($a_options->yaxis))
		{
			$a_options->{"yaxis"} = new stdClass();
		}

		return true;
	}

	protected function parseMinMaxOptions(&$a_options)
	{
		if(isset($this->min_axis["x"]) && $this->checkXAxis($a_options))
		{
			$a_options->xaxis->min = $this->min_axis["x"];
		}
		if(isset($this->min_axis["y"]) && $this->checkYAxis($a_options))
		{
			$a_options->yaxis->min = $this->min_axis["y"];
		}

		if(isset($this->max_axis["x"]) && $this->checkXAxis($a_options))
		{
			$a_options->xaxis->max = $this->max_axis["x"];
		}
		if(isset($this->max_axis["y"]) && $this->checkXAxis($a_options))
		{
			$a_options->yaxis->max = $this->max_axis["y"];
		}
	}
}
	
?>