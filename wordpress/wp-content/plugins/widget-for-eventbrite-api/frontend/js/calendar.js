(function ($) {
    'use strict';

    $(function () {

// http://qtip2.com/options
        var tips = {
            eventRender: function (event, element) {
                element.qtip({
                    content: {
                        title: {text: event.title},
                        text: event.excerpt
                    },
                    style: { classes: 'qtip-blue' },
                    position: { my: 'bottom center',
                                at: 'top center'}
                });
            }
        };


        $('.wfea-calendar.sametab').fullCalendar($.extend(WFEACalendar, tips));

        var newtab = {
            eventClick: function (event) {
                if (event.url) {
                    window.open(event.url);
                    return false;
                }
            }
        };

        $('.wfea-calendar.newtab').fullCalendar($.extend(WFEACalendar, newtab));
    });

})(jQuery);