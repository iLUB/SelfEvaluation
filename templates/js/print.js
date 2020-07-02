printFeedback = function () {
    this.originalBodyWidht = $("body").width();

    window.onafterprint = function (e) {
        $(window).off('mousemove', window.onafterprint);
        $("body").width(this.originalBodyWidht);
        console.log("On After Print");
    };

    $.when($("body").width(800)).then(
        function () {
            setTimeout(
                function () {
                    window.print();
                    setTimeout(function () {
                        $(window).one('mousemove', window.onafterprint);
                        console.log("On After One");

                    }, 1)
                }, 500)
        }
    );
};