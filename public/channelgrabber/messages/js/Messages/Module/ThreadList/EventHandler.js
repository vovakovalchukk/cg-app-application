define([
    'jquery',
    'Messages/Module/EventHandlerAbstract',
    'Messages/Module/ThreadList/Events',
    'Messages/Module/Filter/Events'
], function(
    $,
    EventHandlerAbstract,
    ThreadListEvents,
    FilterEvents
) {
    var EventHandler = function(module)
    {
        EventHandlerAbstract.call(this, module);

        var init = function()
        {
            this.listenForFilterApplyRequested()
                .listenForThreadDomSelection();
        };
        init.call(this);
    };

    EventHandler.SELECTOR_THREAD = '.message-pane ul li';

    EventHandler.prototype = Object.create(EventHandlerAbstract.prototype);

    EventHandler.prototype.listenForFilterApplyRequested = function()
    {
        var self = this;
        $(document).on(FilterEvents.APPLY_REQUESTED, function(event, filter)
        {
            self.getModule().loadForFilter(filter);
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

    EventHandler.prototype.triggerThreadSelected = function(thread)
    {
        $(document).trigger(ThreadListEvents.THREAD_SELECTED, [thread]);
    };

    EventHandler.prototype.triggerThreadsRendered = function(threads)
    {
        $(document).trigger(ThreadListEvents.THREADS_RENDERED, [threads]);
    };

    return EventHandler;
});