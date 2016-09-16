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
        var page = 1;
        var sortDescending = true;
        var previousFilter;

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

        this.setPage = function(newPage)
        {
            page = newPage;
            return this;
        };

        this.getPage = function()
        {
            return page;
        };

        this.setSortDescending = function(newSortDescending)
        {
            sortDescending = newSortDescending;
            return this;
        };

        this.isSortDescending = function()
        {
            return sortDescending;
        };

        this.setPreviousFilter = function(newPreviousFilter)
        {
            previousFilter = newPreviousFilter;
            return this;
        };

        this.getPreviousFilter = function()
        {
            return previousFilter;
        };

        var init = function()
        {
            this.setEventHandler(new EventHandler(this));
        };
        init.call(this);
    };

    ThreadList.TEMPLATE_SUMMARY = '/channelgrabber/messages/template/Messages/ThreadList/summary.mustache';
    ThreadList.SELECTOR_LIST = '.message-pane ul';
    ThreadList.SELECTOR_LIST_ELEMENTS = '.message-pane ul li';

    ThreadList.prototype = Object.create(ModuleAbstract.prototype);

    ThreadList.prototype.loadForFilter = function(filter, selectedThreadId, isNextPage)
    {
        var self = this;

        if (!isNextPage) {
            this.setPage(1);
        }
        this.setPreviousFilter(filter);
        this.getApplication().busy();
        this.getService().fetchCollectionByFilter(filter, this.getPage(), this.isSortDescending(), function(threads)
        {
            if (threads.count() == 100) {
                self.getDomManipulator().show(self.getEventHandler().getSelectorNextPage());
            } else {
                self.getDomManipulator().hide(self.getEventHandler().getSelectorNextPage());
            }
            if (isNextPage) {
                var currentThreads = self.getThreads();
                threads.each(function(thread) {
                    currentThreads.attach(thread);
                });
                threads = currentThreads;
            }
            self.setThreads(threads);
            self.renderThreads(threads, selectedThreadId);
            self.getApplication().unbusy();
        }, function(response)
        {
            self.getApplication().unbusy();
            n.ajaxError(response);
        });
    };

    ThreadList.prototype.changeSortDirection = function()
    {
        this.setSortDescending(!this.isSortDescending());
        this.getDomManipulator().toggleClass(this.getEventHandler().getSelectorSortOrder(), 'ascending');
        this.loadForFilter(this.getPreviousFilter(), null);
    };

    ThreadList.prototype.loadNextPage = function()
    {
        this.setPage(this.getPage() + 1);
        this.loadForFilter(this.getPreviousFilter(), null, true);
    };

    ThreadList.prototype.renderThreads = function(threads, selectedThreadId)
    {
        var self = this;
        CGMustache.get().fetchTemplate(ThreadList.TEMPLATE_SUMMARY, function(template, cgmustache) {
            self.getDomManipulator().remove(ThreadList.SELECTOR_LIST_ELEMENTS);
            threads.each(function(thread)
            {
                var updatedParts = thread.getUpdated().split(' ');
                var html = cgmustache.renderTemplate(template, {
                    'id': thread.getId(),
                    'channel': thread.getChannel(),
                    'name': thread.getName(),
                    'subject': thread.getSubject(),
                    'updatedDate': updatedParts[0],
                    'updatedTime': updatedParts[1],
                    'status': thread.getStatus().replace(/ /g, '-').toLowerCase(),
                    'statusText': thread.getStatus().replace(/_-/g, ' ').ucfirst(),
                    'assignedUserName': thread.getAssignedUserName(),
                    'singleUserMode': self.getApplication().getSingleUserMode()
                });
                self.getDomManipulator().append(ThreadList.SELECTOR_LIST, html);
            });
            // Tell listeners we've rendered. Expected to be picked up by Module/ThreadDetails/Eventhandler
            self.getEventHandler().triggerThreadsRendered(threads);
            if (selectedThreadId) {
                self.threadSelected(selectedThreadId);
            }
        });
    };

    ThreadList.prototype.threadSelected = function(id)
    {
        if (!this.getThreads().containsId(id)) {
            return;
        }
        this.getDomManipulator().removeClass(ThreadList.SELECTOR_LIST_ELEMENTS, 'active')
            .addClass(ThreadList.SELECTOR_LIST_ELEMENTS+'[data-entity-id='+id+']', 'active')

        var thread = this.getThreads().getById(id);
        // The actual fetching and rendering of the thread in the right pane is handled by ThreadDetail
        // This will also be picked up by Application/EventHandler to change the URL
        this.getEventHandler().triggerThreadSelected(thread);
    };

    return ThreadList;
});
