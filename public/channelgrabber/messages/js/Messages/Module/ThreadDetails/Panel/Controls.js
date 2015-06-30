define([
    'Messages/Module/ThreadDetails/PanelAbstract',
    'Messages/Module/ThreadDetails/Panel/Controls/EventHandler',
    'cg-mustache'
], function(
    PanelAbstract,
    EventHandler,
    CGMustache
) {
    var Controls = function(thread, assignableUsers)
    {
        PanelAbstract.call(this, thread);

        this.getAssignableUsers = function()
        {
            return assignableUsers;
        };

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

    Controls.prototype.nonTakableStatuses = {'resolved': true};
    Controls.prototype.nonResolvableStatuses = {'resolved': true};

    Controls.prototype.render = function(thread)
    {
        var self = this;
        CGMustache.get().fetchTemplate(Controls.TEMPLATE, function(template, cgmustache) {
            var html = cgmustache.renderTemplate(template, {
                'subject': thread.getSubject(),
                'channel': thread.getChannel(),
                'accountName': thread.getAccountName(),
                'name': thread.getName(),
                'status': thread.getStatus().replace(/ /g, '-').toLowerCase(),
                'statusText': thread.getStatus().replace(/_-/g, ' ').ucfirst(),
                'assignedUserId': thread.getAssignedUserId(),
                'takable': !(self.nonTakableStatuses[thread.getStatus()]),
                'resolvable': !(self.nonResolvableStatuses[thread.getStatus()])
            });
            self.getDomManipulator().append(PanelAbstract.SELECTOR_CONTAINER, html);
        });
    };

    return Controls;
});