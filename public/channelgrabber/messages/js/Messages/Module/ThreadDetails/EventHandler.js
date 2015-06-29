define([
    'jquery',
    'Messages/Module/EventHandlerAbstract',
    'Messages/Module/ThreadList/Events'
], function(
    $,
    EventHandlerAbstract,
    ThreadListEvents
) {
    var EventHandler = function(module)
    {
        EventHandlerAbstract.call(this, module);

        var init = function()
        {
            this.listenForThreadSelection()
                .listenForThreadsRendered();
        };
        init.call(this);
    };

    EventHandler.prototype = Object.create(EventHandlerAbstract.prototype);

    EventHandler.prototype.listenForThreadSelection = function()
    {
        var self = this;
        $(document).on(ThreadListEvents.THREAD_SELECTED, function(event, thread)
        {
            self.getModule().loadThread(thread);
        });
        return this;
    };

    EventHandler.prototype.listenForThreadsRendered = function()
    {
        var self = this;
        $(document).on(ThreadListEvents.THREADS_RENDERED, function()
        {
            self.getModule().resetPanels();
        });
        return this;
    };

    return EventHandler;
});