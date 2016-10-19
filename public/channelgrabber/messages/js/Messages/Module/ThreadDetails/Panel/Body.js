define([
    'Messages/Module/ThreadDetails/PanelAbstract',
    'Messages/Module/ThreadDetails/Panel/Body/EventHandler',
    'Messages/Thread/Service',
    'cg-mustache'
], function(
    PanelAbstract,
    EventHandler,
    threadService,
    CGMustache
) {
    var Body = function(module, thread)
    {
        PanelAbstract.call(this, module, thread);

        this.getThreadService = function()
        {
            return threadService;
        };

        var init = function()
        {
            this.setEventHandler(new EventHandler(this));
            this.render(thread);
        };
        init.call(this);
    };

    Body.SELECTOR = '.message-section';
    Body.COUNT_SELECTOR = '.message-:type .count'
    Body.PRINT_CLASS = 'print-message';
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
                'body': message.getBody(),
                'iconClass': iconClass
            });
        });
        CGMustache.get().fetchTemplate(Body.TEMPLATE, function(template, cgmustache) {
            var html = cgmustache.renderTemplate(template, {
                'messages': messagesData
            });
            self.getDomManipulator().append(PanelAbstract.SELECTOR_CONTAINER, html);
            self.updateCounts(thread);
        });
    };

    Body.prototype.updateCounts = function(thread)
    {
        var self = this;
        this.getThreadService().fetchCounts(thread.getId(), function(counts) {
            for (var type in counts) {
                var selector = Body.COUNT_SELECTOR.replace(":type", type);
                self.getDomManipulator().setHtml(selector, counts[type]);
            }
        });
    };

    Body.prototype.print = function(message)
    {
        var self = this;
        self.getDomManipulator().removeClass(Body.SELECTOR + " ." + Body.PRINT_CLASS, Body.PRINT_CLASS);
        self.getDomManipulator().addClass(message, Body.PRINT_CLASS);
        window.print();
    };

    return Body;
});
