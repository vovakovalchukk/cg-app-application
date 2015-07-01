define([
    'Messages/Module/ThreadDetails/PanelAbstract',
    'Messages/Module/ThreadDetails/Panel/Respond/EventHandler',
    'Messages/Thread/Message/Service',
    'cg-mustache'
], function(
    PanelAbstract,
    EventHandler,
    service,
    CGMustache
) {
    var Respond = function(thread)
    {
        PanelAbstract.call(this, thread);

        this.getService = function()
        {
            return service;
        };

        var init = function()
        {
            this.setEventHandler(new EventHandler(this));
            this.render(thread);
        };
        init.call(this);
    };

    Respond.SELECTOR = '.reply-section';
    Respond.SELECTOR_MESSAGE = '#respond-message';
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

    Respond.prototype.send = function()
    {
        this.sendMessage(false);
    };

    Respond.prototype.sendAndResolve = function()
    {
        this.sendMessage(true);
    };

    Respond.prototype.sendMessage = function(resolve)
    {
        var self = this;
        var messageBody = this.getDomManipulator().getValue(Respond.SELECTOR_MESSAGE).trim();
        if (!messageBody) {
            return;
        }
        n.notice('Sending message...');
        this.getService().sendMessage(this.getThread(), messageBody, resolve, function(message)
        {
            self.getThread().getMessages().attach(message);
            n.success('Your message has been sent');
            // Tell listeners a message has been added. Expected to be picked up by Module\Filter\EventHandler
            self.getEventHandler().triggerMessageAdded(message, resolve, self.getThread());
        });
    };

    return Respond;
});