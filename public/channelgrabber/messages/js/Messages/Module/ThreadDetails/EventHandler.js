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
            this.listenForThreadSelection();
        };
        init.call(this);
    };

    EventHandler.prototype = Object.create(EventHandlerAbstract.prototype);

    EventHandler.prototype.listenForThreadSelection = function()
    {
        var self = this;
        $(document).on(ThreadListEvents.THREAD_SELECTED, function(event, thread)
        {
            self.getModule().threadSelected(thread);
        });
        return this;
    };

    return EventHandler;
});