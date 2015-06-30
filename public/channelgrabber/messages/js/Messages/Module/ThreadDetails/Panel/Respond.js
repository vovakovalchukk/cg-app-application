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
    Respond.TEMPLATE_BUTTONS = '/channelgrabber/zf2-v4-ui/templates/elements/buttons.mustache';

    Respond.prototype = Object.create(PanelAbstract.prototype);

    Respond.prototype.render = function(thread)
    {
        var self = this;
        CGMustache.get().fetchTemplates({main: Respond.TEMPLATE, buttons: Respond.TEMPLATE_BUTTONS}, function(templates, cgmustache) {
            var buttonHtml = cgmustache.renderTemplate(templates, {
                'buttons': [{
                    'id': 'respond-send-resolve',
                    'action': 'send-resolve',
                    'value': 'Send & Resolve',
                    'type': 'button',
                    'class': 'blue'
                }, {
                    'id': 'respond-send',
                    'action': 'send',
                    'value': 'Send',
                    'type': 'button'
                }]
            }, 'buttons');
            var html = cgmustache.renderTemplate(templates, {
                'threadId': thread.getId()
            }, 'main', {buttons: buttonHtml});
            self.getDomManipulator().append(PanelAbstract.SELECTOR_CONTAINER+' .message-section', html);
        });
    };

    return Respond;
});