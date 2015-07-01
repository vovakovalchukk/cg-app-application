define([
    'jquery',
    'Messages/Application/Events',
    'Messages/Module/ThreadList/Events'
], function(
    $,
    Events,
    ThreadListEvents
) {
    var EventHandler = function(application)
    {
        this.getApplication = function()
        {
            return application;
        };

        var init = function()
        {
            this.listenForThreadSelected();
        };
        init.call(this);
    };

    EventHandler.prototype.listenForThreadSelected = function()
    {
        var self = this;
        $(document).on(ThreadListEvents.THREAD_SELECTED, function(event, thread)
        {
            self.getApplication().setUrlForThread(thread);
        });
        return this;
    };

    EventHandler.prototype.triggerInitialised = function(selectedThreadId)
    {
        $(document).trigger(Events.INITIALISED, [selectedThreadId]);
    };

    return EventHandler;
});