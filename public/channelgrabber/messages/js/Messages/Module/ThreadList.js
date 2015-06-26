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

        var threads;

        this.getService = function()
        {
            return service;
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
            this.setEventHandler(new EventHandler(this));
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
        });
    };

    ThreadList.prototype.renderThreads = function(threads)
    {
        // TODO: CGIV-5839
        this.getEventHandler().triggerThreadsRendered(threads);
    };

    ThreadList.prototype.threadSelected = function(id)
    {
        if (!this.getThreads().containsId(id)) {
            return;
        }
        var thread = this.getThreads().getById(id);
        // TODO: CGIV-5839, highlight this thread in the list

        // The actual fetching and rendering of the thread in the right pane is handled by ThreadDetail
        this.getEventHandler().triggerThreadSelected(thread);
    };

    return ThreadList;
});