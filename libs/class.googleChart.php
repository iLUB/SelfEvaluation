<?php

/**
 * googleChart Class
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version
 */
class googleChart {

	//
	// ChartTypes
	//
	const TYPE_PIE = 'PieChart';
	const TYPE_LINE = 'LineChart';
	const TYPE_BAR = 'BarChart';
	const TYPE_AREA = 'AreaChart';
	const TYPE_CANDLESTICK = 'CandlestickChart';
	//
	// Fields
	//
	/**
	 * @var bool
	 */
	private static $first = true;
	/**
	 * @var int
	 */
	private static $count = 0;
	/**
	 * @var string
	 */
	private $chart_type;
	/**
	 * @var array
	 */
	private $data = array();
	/**
	 * @var bool
	 */
	private $skip_first_row = false;
	/**
	 * @var string
	 */
	private $div = '';
	/**
	 * @var string
	 */
	protected $output = '';


	/**
	 * @param string $chartType
	 * @param        $div
	 * @param bool   $skipFirstRow
	 */
	public function __construct($chartType = self::TYPE_PIE, $div = NULL, $skipFirstRow = false) {
		$this->setChartType($chartType);
		$this->setSkipFirstRow($skipFirstRow);
		$this->setDiv($div);
		self::$count ++;
	}


	/**
	 * @param        $data
	 * @param string $dataType
	 */
	public function load($data, $dataType = 'json') {
		$this->setData(($dataType != 'json') ? $this->dataToJson($data) : $data);
	}


	/**
	 * @return string
	 */
	private function initChart() {
		self::$first = false;
		$this->output = '';
		// start a code block
		$this->output .= '<script type="text/javascript" src="https://www.google.com/jsapi"></script>' . PHP_EOL;
		$this->output .= '<script type="text/javascript">google.load(\'visualization\', \'1.0\', {\'packages\':[\'corechart\']});</script>' . PHP_EOL;

		return $this->output;
	}


	/**
	 * @param       $div
	 * @param array $options
	 *
	 * @return string
	 */
	public function draw($div, Array $options = array()) {
		$this->output = '';
		if (self::$first) {
			$this->output .= $this->initChart();
		}
		// start a code block
		$this->output .= '<script type="text/javascript">';
		// set callback function
		$this->output .= 'google.setOnLoadCallback(drawChart' . self::$count . ');';
		// $this->output callback function
		$this->output .= 'function drawChart' . self::$count . '(){';
		$this->output .= 'var data = new google.visualization.DataTable(' . $this->getData() . ');';
		// set the options
		$this->output .= 'var options = ' . json_encode($options) . ';';
		// create and draw the chart
		$this->output .= 'var chart = new google.visualization.' . $this->getChartType() . '(document.getElementById(\''
			. $div . '\'));';
		$this->output .= 'chart.draw(data, options);';
		$this->output .= '} </script>' . PHP_EOL;

		return $this->output;
	}


	/**
	 * @param $data
	 *
	 * @return array
	 */
	private function getColumns($data) {
		$cols = array();
		foreach ($data[0] as $key => $value) {
			if (is_numeric($key)) {
				if (is_string($data[1][$key])) {
					$cols[] = array( 'id' => '', 'label' => $value, 'type' => 'string' );
				} else {
					$cols[] = array( 'id' => '', 'label' => $value, 'type' => 'number' );
				}
				$this->skip_first_row = true;
			} else {
				if (is_string($value)) {
					$cols[] = array( 'id' => '', 'label' => $key, 'type' => 'string' );
				} else {
					$cols[] = array( 'id' => '', 'label' => $key, 'type' => 'number' );
				}
			}
		}

		return $cols;
	}


	/**
	 * @param $data
	 *
	 * @description info: http://code.google.com/intl/nl-NL/apis/chart/interactive/docs/datatables_dataviews.html#javascriptliteral
	 *
	 * @return string
	 */
	private function dataToJson($data) {
		$cols = $this->getColumns($data);
		$rows = array();
		foreach ($data as $key => $row) {
			if ($key != 0 || ! $this->skip_first_row) {
				$c = array();
				foreach ($row as $v) {
					$c[] = array( 'v' => $v );
				}
				$rows[] = array( 'c' => $c );
			}
		}

		return json_encode(array( 'cols' => $cols, 'rows' => $rows ));
	}


	/**
	 * @param string $chart_type
	 */
	public function setChartType($chart_type) {
		$this->chart_type = $chart_type;
	}


	/**
	 * @return string
	 */
	public function getChartType() {
		return $this->chart_type;
	}


	/**
	 * @param int $count
	 */
	public static function setCount($count) {
		self::$count = $count;
	}


	/**
	 * @return int
	 */
	public static function getCount() {
		return self::$count;
	}


	/**
	 * @param array $data
	 */
	public function setData($data) {
		$this->data = $data;
	}


	/**
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}


	/**
	 * @param string $div
	 */
	public function setDiv($div) {
		$this->div = $div;
	}


	/**
	 * @return string
	 */
	public function getDiv() {
		return $this->div;
	}


	/**
	 * @param boolean $first
	 */
	public static function setFirst($first) {
		self::$first = $first;
	}


	/**
	 * @return boolean
	 */
	public static function getFirst() {
		return self::$first;
	}


	/**
	 * @param boolean $skip_first_row
	 */
	public function setSkipFirstRow($skip_first_row) {
		$this->skip_first_row = $skip_first_row;
	}


	/**
	 * @return boolean
	 */
	public function getSkipFirstRow() {
		return $this->skip_first_row;
	}


	/**
	 * @param string $output
	 */
	public function setOutput($output) {
		$this->output = $output;
	}


	/**
	 * @return string
	 */
	public function getOutput() {
		return $this->output;
	}
}

?>
