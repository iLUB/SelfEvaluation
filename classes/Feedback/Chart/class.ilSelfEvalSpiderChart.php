<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Chart/classes/class.ilChartSpider.php";
include_once "trait.ilSelfEvalChartTrait.php";
/**
 * Class ilSelfEvalBarChart
 */
class ilSelfEvalSpiderChart extends ilChartSpider{
    use ilSelfEvalChartTrait;

    public function __construct($a_id){
        parent::__construct($a_id);
        $this->setSize($this->getCanvasWidth(), $this->getCanvasHeight());
        $this->setColors($this->getChartColors());
        $this->setLegend($this->getLegend());
        $this->setAutoResize(true);
    }
}