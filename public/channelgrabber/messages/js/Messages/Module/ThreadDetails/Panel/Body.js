define([
    'Messages/Module/ThreadDetails/PanelAbstract',
    'Messages/Module/ThreadDetails/Panel/Body/EventHandler',
    'cg-mustache'
], function(
    PanelAbstract,
    EventHandler,
    CGMustache
) {
    var Body = function(thread)
    {
        PanelAbstract.call(this, thread);

        var init = function()
        {
            this.setEventHandler(new EventHandler(this));
            this.render(thread);
        };
        init.call(this);
    };

    Body.SELECTOR = '.message-section';
    Body.TEMPLATE = '/channelgrabber/messages/template/Messages/ThreadDetails/Panel/body.mustache';

    Body.prototype = Object.create(PanelAbstract.prototype);

    Body.prototype.render = function(thread)
    {
        var self = this;
        var messagesData = [];
        thread.getMessages().each(function(message)
        {
            var iconClass = (message.getPersonType() == 'staff' ? 'sprite-message-staff-21-blue' : 'sprite-message-customer-21-red');
            messagesData.push({
                'name': message.getName(),
                'externalUsername': message.getExternalUsername(),
                'created': message.getCreated(),
                'createdFuzzy': message.getCreatedFuzzy(),
                'body': message.getBody().nl2br(),
                'iconClass': iconClass
            });
        });
        CGMustache.get().fetchTemplate(Body.TEMPLATE, function(template, cgmustache) {
            var html = cgmustache.renderTemplate(template, {
                'messages': messagesData
            });
            self.getDomManipulator().append(PanelAbstract.SELECTOR_CONTAINER, html);
        });
    };

    return Body;
});