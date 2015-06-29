define([
    'Messages/Module/ThreadDetails/PanelAbstract',
    'Messages/Module/ThreadDetails/Panel/Respond/EventHandler',
    'cg-mustache'
], function(
    PanelAbstract,
    EventHandler,
    CGMustache
) {
    var Respond = function(thread)
    {
        PanelAbstract.call(this, thread);

        var init = function()
        {
            this.setEventHandler(new EventHandler(this));
            this.render(thread);
        };
        init.call(this);
    };

    Respond.SELECTOR = '.reply-section';
    Respond.TEMPLATE = '/channelgrabber/messages/template/Messages/ThreadDetails/Panel/respond.mustache';

    Respond.prototype = Object.create(PanelAbstract.prototype);

    Respond.prototype.render = function(thread)
    {
        var self = this;
        CGMustache.get().fetchTemplate(Respond.TEMPLATE, function(template, cgmustache) {
            var html = cgmustache.renderTemplate(template, {
                'threadId': thread.getId()
            });
            self.getDomManipulator().append(PanelAbstract.SELECTOR_CONTAINER+' .message-section', html);
        });
    };

    return Respond;
});