define([
    'Messages/ModuleAbstract',
    'Messages/Module/ThreadList/EventHandler',
    'Messages/Thread/Service',
    'cg-mustache',
    'DomManipulator'
], function(
    ModuleAbstract,
    EventHandler,
    service,
    CGMustache,
    domManipulator
) {
    var ThreadList = function(application)
    {
        ModuleAbstract.call(this, application);

        var threads;

        this.getService = function()
        {
            return service;
        };

        this.getDomManipulator = function()
        {
            return domManipulator;
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

    ThreadList.TEMPLATE_SUMMARY = '/channelgrabber/messages/template/Messages/Thread/summary.mustache';
    ThreadList.SELECTOR_LIST = '.message-pane ul';
    ThreadList.SELECTOR_LIST_ELEMENTS = '.message-pane ul li';

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
        var self = this;
        CGMustache.get().fetchTemplate(ThreadList.TEMPLATE_SUMMARY, function(template, cgmustache) {
            self.getDomManipulator().remove(ThreadList.SELECTOR_LIST_ELEMENTS);
            threads.each(function(thread)
            {
                var html = cgmustache.renderTemplate(template, {
                    'id': thread.getId(),
                    'channel': thread.getChannel(),
                    'name': thread.getName(),
                    'subject': thread.getSubject(),
                    'created': thread.getCreated(),
                    'updated': thread.getUpdated(),
                    'status': thread.getStatus()
                });
                self.getDomManipulator().append(ThreadList.SELECTOR_LIST, html);
            });
            self.getEventHandler().triggerThreadsRendered(threads);
        });
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