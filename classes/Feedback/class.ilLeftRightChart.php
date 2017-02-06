<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Chart/classes/class.ilChart.php";

/**
 * Generator for grid-based charts
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 */
class ilLeftRightChart extends ilChartGrid
{
	const TYPE_LEFT_RIGHT = 99;

	/**
	 * @param int $a_type
	 * @param string $a_id
	 * @return ilChart|ilLeftRightChart
	 */
	public static function getInstanceByType($a_type, $a_id)
	{
		switch($a_type)
		{
			case self::TYPE_LEFT_RIGHT:
				include_once "Services/Chart/classes/class.ilChartSpider.php";
				return new self($a_id);
		}
		return parent::getInstanceByType($a_type, $a_id);
	}

	/**
	 * @param ilChartData $a_series
	 * @return bool
	 */
	protected function isValidDataType(ilChartData $a_series)
	{
		if($a_series instanceof ilChartDataLeftRight)
		{
			return true;
		}
		return false;
	}

	/**
	 * @param stdClass $a_options
	 */
	public function parseGlobalOptions(stdClass $a_options)
	{
		parent::parseGlobalOptions($a_options);
		$a_options->{"yaxis"}->labelWidth = 0;

		$a_options->{"yaxis"}->min = min(array_keys($this->getTicks()["y"]));
		$a_options->{"yaxis"}->max = max(array_keys($this->getTicks()["y"]));

		$a_options->{"xaxis"}->min = min(array_keys($this->getTicks()["x"]));
		$a_options->{"xaxis"}->max = max(array_keys($this->getTicks()["x"]));
	}
}