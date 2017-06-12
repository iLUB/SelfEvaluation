<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Chart/classes/class.ilChartDataLines.php";

/**
 * Chart data left right series
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 */
class ilChartDataLeftRight extends ilChartDataLines
{
	protected function parseDataOptions(array &$a_options)
	{
		parent::parseDataOptions($a_options);
	}
}