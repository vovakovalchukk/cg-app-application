define([
    'cg-mustache'
], function(
    CGMustache
) {
    function TimelineService() {
    }

    TimelineService.TIMELINE_TEMPLATE_PATH = '/cg-built/zf2-v4-ui/templates/elements/timeline-boxes.mustache';
    TimelineService.TIMELINE_CONTAINER_SELECTOR = '#timeline';


    TimelineService.prototype.refresh = function (timelineData) {
        var self = this;
        CGMustache.get().fetchTemplate(TimelineService.TIMELINE_TEMPLATE_PATH, function(template, cgMustache)
        {
            self.replaceTimeline(template, timelineData);
        });
    };

    TimelineService.prototype.replaceTimeline = function (template, data) {
        var contents = CGMustache.get().renderTemplate(template, data);
        $(TimelineService.TIMELINE_CONTAINER_SELECTOR).html(contents);
    };

    return new TimelineService;
});