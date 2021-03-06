<?php
namespace ilub\plugin\SelfEvaluation\UIHelper\Chart;

use ilChartLegend;

trait ChartHelper
{
    protected $canvas_width = "99%";
    protected $canvas_height = "450px";

    protected function getLegend() : ilChartLegend
    {
        $legend = new ilChartLegend();
        $legend->setBackground($this->getChartColors()[0]);
        return $legend;
    }

    protected function getBackgroundColor() : string
    {
        return $this->getChartColors()[0];
    }

    protected function getChartColors() : array
    {
        return [
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
        ];
    }

    public function getCanvasWidth() : string
    {
        return $this->canvas_width;
    }

    public function setCanvasWidth(string $canvas_width)
    {
        $this->canvas_width = $canvas_width;
    }

    public function getCanvasHeight() : string
    {
        return $this->canvas_height;
    }

    public function setCanvasHeight(string $canvas_height)
    {
        $this->canvas_height = $canvas_height;
    }
}