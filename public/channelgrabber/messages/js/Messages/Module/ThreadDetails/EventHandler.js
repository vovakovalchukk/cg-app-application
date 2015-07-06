define([
    'jquery',
    'Messages/Module/EventHandlerAbstract',
    'Messages/Module/ThreadList/Events',
    'Messages/Module/ThreadDetails/Panel/Controls/Events'
], function(
    $,
    EventHandlerAbstract,
    ThreadListEvents,
    ControlEvents
) {
    var EventHandler = function(module)
    {
        EventHandlerAbstract.call(this, module);

        var init = function()
        {
            this.listenForThreadSelection()
                .listenForThreadChanges();
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

    EventHandler.prototype.listenForThreadChanges = function()
    {
        var self = this;
        $(document).on(ControlEvents.ASSIGNEE_CHANGED+' '+ControlEvents.STATUS_CHANGED, function()
        {
            self.getModule().refresh();
        });
        return this;
    };

    return EventHandler;
});