define([
    'Messages/Module/ThreadDetails/PanelAbstract',
    'Messages/Module/ThreadDetails/Panel/Controls/EventHandler',
    'cg-mustache'
], function(
    PanelAbstract,
    EventHandler,
    CGMustache
) {
    var Controls = function(thread)
    {
        PanelAbstract.call(this, thread);

        var init = function()
        {
            this.setEventHandler(new EventHandler(this));
            this.render(thread);
        };
        init.call(this);
    };

    Controls.SELECTOR = '.preview-header';
    Controls.TEMPLATE = '/channelgrabber/messages/template/Messages/ThreadDetails/Panel/controls.mustache';

    Controls.prototype = Object.create(PanelAbstract.prototype);

    Controls.prototype.render = function(thread)
    {
        var self = this;
        CGMustache.get().fetchTemplate(Controls.TEMPLATE, function(template, cgmustache) {
            var html = cgmustache.renderTemplate(template, {
                'subject': thread.getSubject(),
                'channel': thread.getChannel(),
                'account': '<account name>',
                'name': thread.getName(),
                'status': thread.getStatus().toLowerCase(),
                'statusText': thread.getStatus().replace(/_-/g, ' ').ucfirst(),
                'assignedUserId': thread.getAssignedUserId()
            });
            self.getDomManipulator().append(PanelAbstract.SELECTOR_CONTAINER, html);
        });
    };

    return Controls;
});