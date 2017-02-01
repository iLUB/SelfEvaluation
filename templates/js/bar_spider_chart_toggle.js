xsevChartToggle = function(parent_id) {
	this.parent = $('#' + parent_id);
	this.bar_chart_button = this.parent.find('.bar_chart_button');
	this.spider_chart_button = this.parent.find('.spider_chart_button');
	this.left_right_chart_button = this.parent.find('.left_right_chart_button');

	this.bar_chart = this.parent.find('.bar_chart');
	this.spider_chart = this.parent.find('.spider_chart');
	this.left_right_chart = this.parent.find('.left_right_chart');
	var self = this;

	console.log(self.spider_chart.find(".flot-overlay"));


	this.hideIfLoaded = function(depth) {
		if((self.spider_chart.find("canvas").length && self.left_right_chart.find("canvas").length) ||
				depth > 100){

			self.spider_chart.hide();
			self.left_right_chart.hide();
		}else{
			setTimeout(function() {self.hideIfLoaded(++depth)}, 50);
		}
	};

	this.hideIfLoaded(0);

	this.deactivateButtons = function(){
		self.bar_chart_button.removeClass("active");
		self.spider_chart_button.removeClass("active");
		self.left_right_chart_button.removeClass("active");

		self.bar_chart.hide();
		self.spider_chart.hide();
		self.left_right_chart.hide();
	};

	this.bar_chart_button.click(function() {
		self.deactivateButtons(self);
		self.bar_chart_button.addClass("active");
		self.bar_chart.show();
		return false;
	});

	this.spider_chart_button.click(function() {
		self.deactivateButtons(self);
		self.spider_chart_button.addClass("active");
		self.spider_chart.show();
		return false;
	});

	this.left_right_chart_button.click(function() {
		self.deactivateButtons(self);
		self.left_right_chart_button.addClass("active");
		self.left_right_chart.show();
		return false;
	});
};

