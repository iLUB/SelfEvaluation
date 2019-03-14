xsevChartToggle = function(parent_id) {
	this.parent = $('#' + parent_id);

	if(this.parent.find('button').length == 1){
		this.parent.find('.btn-group').hide();
		return;
	}

	this.bar_chart_button = this.parent.find('.bar_chart_button');
	this.spider_chart_button = this.parent.find('.spider_chart_button');
	this.left_right_chart_button = this.parent.find('.left_right_chart_button');

	this.bar_chart = this.parent.find('.bar_chart');
	this.spider_chart = this.parent.find('.spider_chart');
	this.left_right_chart = this.parent.find('.left_right_chart');
	var self = this;

	this.first_button = null;

	if(this.bar_chart_button.length){
		this.first_button = this.bar_chart_button;
	}else if(this.spider_chart_button.length){
		this.first_button = this.spider_chart_button;
	}else{
		this.first_button = this.left_right_chart_button;
	}

	this.hideIfLoaded = function(depth) {
		if((self.spider_chart.find("canvas").length ||
				self.left_right_chart.find("canvas").length)
				|| depth > 100){

			if(self.spider_chart_button != self.first_button){
				self.spider_chart.hide();
			}
			console.log();
			self.left_right_chart.hide();
		}else{
			setTimeout(function() {self.hideIfLoaded(++depth)}, 100);
		}

        return self;
	};

	this.first_button.addClass("active");
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
        self.printFeedback();

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

