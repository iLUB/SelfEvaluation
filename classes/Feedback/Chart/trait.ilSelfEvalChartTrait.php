<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilSelfEvalChart
 */
trait ilSelfEvalChartTrait
{
    protected $canvas_width = "99%";
    protected $canvas_height = "450px";


    protected function getLegend(){
        $legend = new ilChartLegend();
        $legend->setBackground($this->getChartColors()[0]);
        return $legend;
    }
    /**
     * @return mixed
     */
    protected function getBackgroundColor(){
        return $this->getChartColors()[0];
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

    /**
     * @return string
     */
    public function getCanvasWidth(): string
    {
        return $this->canvas_width;
    }

    /**
     * @param string $canvas_width
     */
    public function setCanvasWidth(string $canvas_width)
    {
        $this->canvas_width = $canvas_width;
    }

    /**
     * @return string
     */
    public function getCanvasHeight(): string
    {
        return $this->canvas_height;
    }

    /**
     * @param string $canvas_height
     */
    public function setCanvasHeight(string $canvas_height)
    {
        $this->canvas_height = $canvas_height;
    }
}