il.xsevChartToggle = function(parent_id) {
	var parent = $('#' + parent_id);
	var toggle_button = parent.find('a.chart_toggle');
	var bar_chart = parent.find('.bar_chart');
	var spider_chart = parent.find('.spider_chart');
	toggle_button.click(function() {
		bar_chart.toggle();
		spider_chart.toggle();
		return false;
	});
};
