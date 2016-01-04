define([
    'jquery',
    'Messages/Module/EventHandlerAbstract',
    'Messages/Module/ThreadList/Events',
    'Messages/Module/Filter/Events',
    'Messages/Thread/Storage/Events'
], function(
    $,
    EventHandlerAbstract,
    ThreadListEvents,
    FilterEvents,
    StorageEvents
) {
    var EventHandler = function(module)
    {
        EventHandlerAbstract.call(this, module);

        var init = function()
        {
            this.listenForThreadFetched()
                .listenForFilterApplyRequested()
                .listenForThreadDomSelection()
                .listenForThreadNextPage();
        };
        init.call(this);
    };

    EventHandler.SELECTOR_THREAD = '.message-pane ul li';
    EventHandler.SELECTOR_NEXT_PAGE = '.message-pane #next-page';

    EventHandler.prototype = Object.create(EventHandlerAbstract.prototype);

    EventHandler.prototype.listenForThreadFetched = function()
    {
        var self = this;
        $(document).on(StorageEvents.THREAD_FETCHED, function(event, thread)
        {
            self.getModule().getThreads().attach(thread);
        });
        return this;
    };

    EventHandler.prototype.listenForFilterApplyRequested = function()
    {
        var self = this;
        $(document).on(FilterEvents.APPLY_REQUESTED, function(event, filter, selectedThreadId)
        {
            self.getModule().loadForFilter(filter, selectedThreadId);
        });
        return this;
    };

    EventHandler.prototype.listenForThreadDomSelection = function()
    {
        var self = this;
        $(document).on('click', EventHandler.SELECTOR_THREAD, function()
        {
            var selectedElement = this;
            var id = $(selectedElement).data('entityId');
            self.getModule().threadSelected(id);
        });
        return this;
    };

    EventHandler.prototype.listenForThreadNextPage = function()
    {
        var self = this;
        $(document).on('click', EventHandler.SELECTOR_NEXT_PAGE, function()
        {
            self.getModule().loadNextPage();
        });
        return this;
    };

    EventHandler.prototype.triggerThreadSelected = function(thread)
    {
        $(document).trigger(ThreadListEvents.THREAD_SELECTED, [thread]);
    };

    EventHandler.prototype.triggerThreadsRendered = function(threads)
    {
        $(document).trigger(ThreadListEvents.THREADS_RENDERED, [threads]);
    };

    EventHandler.prototype.getSelectorNextPage = function()
    {
        return EventHandler.SELECTOR_NEXT_PAGE;
    };

    return EventHandler;
});
