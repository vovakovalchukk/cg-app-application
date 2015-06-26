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
        var threads;

        this.getService = function()
        {
            return service;
        };

        this.getEventHandler = function()
        {
            return eventHandler;
        };

        this.getThreads = function()
        {
            return threads;
        };

        this.setThreads = function(newThreads)
        {
            threads = newThreads;
            return this;
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
            self.setThreads(threads);
            self.renderThreads(threads);
console.log('Threads loaded');
        });
    };

    ThreadList.prototype.renderThreads = function(threads)
    {
        // TODO
        this.getEventHandler().triggerThreadsRendered(threads);
console.log(threads.getItems());
    };

    ThreadList.prototype.threadSelected = function(id)
    {
console.log('Thread '+id+' selected');
        if (!this.getThreads().containsId(id)) {
            return;
        }
        var thread = this.getThreads().getById(id);
        // TODO: highlight this thread in the list
        // The actual fetching and rendering of the thread in the right pane is handled by ThreadDetail
        this.getEventHandler().triggerThreadSelected(thread);
console.log(thread);
    };

    return ThreadList;
});