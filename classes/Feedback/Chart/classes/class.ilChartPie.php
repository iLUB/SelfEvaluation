<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Feedback/Chart/classes/class.ilChart.php";

/**
 * Generator for pie charts
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesChart
 */
class ilChartPie50 extends ilChart50
{
	public function getDataInstance($a_type = null)
	{		
		include_once "Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Feedback/Chart/classes/class.ilChartDataPie.php";
		return new ilChartDataPie50();
	}
	
	protected function isValidDataType(ilChartData50 $a_series)
	{
		return ($a_series instanceof ilChartDataPie50);
	}
	
	protected function addCustomJS()
	{
		global $tpl;
		
		$tpl->addJavascript("Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Feedback/Chart/js/flot/jquery.flot.pie.js");
	}		
	
	public function parseGlobalOptions(stdClass $a_options)
	{
		// if no inner labels set, use legend
		if(!isset($a_options->series->pie->label) && 
			!$this->legend)
		{
			$legend = new ilChartLegend50();
			$legend->setPosition("nw");
			$this->setLegend($legend);			
		}
	}
}

