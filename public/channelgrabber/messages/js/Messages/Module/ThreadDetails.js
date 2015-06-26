define([
    'Messages/ModuleAbstract',
    'Messages/Module/ThreadDetails/EventHandler',
    'Messages/Thread/Service'
], function(
    ModuleAbstract,
    EventHandler,
    service
) {
    var ThreadDetails = function(application)
    {
        ModuleAbstract.call(this, application);

        var eventHandler;
        var thread;

        this.getService = function()
        {
            return service;
        };

        this.getEventHandler = function()
        {
            return eventHandler;
        };

        this.getThread = function()
        {
            return thread;
        };

        this.setThread = function(newThread)
        {
            thread = newThread;
            return this;
        };

        var init = function()
        {
            eventHandler = new EventHandler(this);

console.log('ThreadDetails initialised');
        };
        init.call(this);
    };

    ThreadDetails.prototype = Object.create(ModuleAbstract.prototype);

    ThreadDetails.prototype.threadSelected = function(thread)
    {
        var self = this;
        this.getService().fetch(thread.getId(), function(thread)
        {
            self.setThread(thread);
            self.renderThread(thread);
console.log('Individual Thread loaded');
        });
    };

    ThreadDetails.prototype.renderThread = function(thread)
    {
        // TODO
console.log(thread.getMessages().getItems());
    };

    return ThreadDetails;
});