function scaleUnits(){
    this.full_unit_titels = new Array();

    this.updateScaleUnits = function() {
        var self = this;
        $(".scale-units td div").each(function() {
            var id = $(this).attr("id");
            $(this).text(self.full_unit_titels[id]);
            if ($(this).prop('scrollWidth') > $(this).width()) {
                $(this).attr("data-toggle", "tooltip");
                $(this).text(function () {
                    return $(this).text().substring(0,3)+"...";
                });
                $(this).tooltip('enable');
                $(this).tooltip({trigger:'hover'});
            }
            else{
                $(this).tooltip('disable');
                $(this).text(self.full_unit_titels[id]);
            }
        });
    }

    this.scaleMatrix = function() {
        $(".matrix-row-input").each(function() {
            parent_object = $(this).parents(".block-question");
            $(this).height(parent_object.children(".question-text").outerHeight());
        });
    }

    $( window ).resize(function() {
        self.updateScaleUnits();
        self.scaleMatrix();

    });

    $( window ).load(function() {
        var self = this;
        $(".scale-units td div").each(function() {
            var id = $(this).attr("id");
            self.full_unit_titels[id] = $(this).text();
        });

        self.updateScaleUnits();
        self.scaleMatrix();
    });
}