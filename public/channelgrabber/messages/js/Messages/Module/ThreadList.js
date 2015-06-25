define([
    'Messages/ModuleAbstract',
    'Messages/Module/ThreadList/EventHandler',
    'Messages/Thread/Service'
], function(
    ModuleAbstract,
    EventHandler,
    service
) {
    var ThreadList = function(application)
    {
        ModuleAbstract.call(this, application);

        var eventHandler;

        this.getService = function()
        {
            return service;
        };

        this.getEventHandler = function()
        {
            return eventHandler;
        };

        var init = function()
        {
            eventHandler = new EventHandler(this);

console.log('ThreadList initialised');
        };
        init.call(this);
    };

    ThreadList.prototype = Object.create(ModuleAbstract.prototype);

    ThreadList.prototype.loadForFilter = function(filter)
    {
        var self = this;
        this.getService().fetchCollectionByFilter(filter, function(threads)
        {
            self.renderThreads(threads);
console.log('Threads loaded');
        });
    };

    ThreadList.prototype.renderThreads = function(threads)
    {
        // TODO
console.log(threads.getItems());
    };

    return ThreadList;
});