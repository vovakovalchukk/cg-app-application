define([
    'jquery',
    'Messages/Module/EventHandlerAbstract',
    'Messages/Module/ThreadList/Events',
    'Messages/Module/ThreadDetails/Panel/Respond/Events'
], function(
    $,
    EventHandlerAbstract,
    ThreadListEvents,
    RespondEvents
) {
    var EventHandler = function(module)
    {
        EventHandlerAbstract.call(this, module);

        var init = function()
        {
            this.listenForThreadSelection()
                .listenForThreadsRendered()
                .listenForMessageAdded();
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

    EventHandler.prototype.listenForMessageAdded = function()
    {
        var self = this;
        $(document).on(RespondEvents.MESSAGE_ADDED, function()
        {
            self.getModule().refresh();
        });
        return this;
    };

    return EventHandler;
});