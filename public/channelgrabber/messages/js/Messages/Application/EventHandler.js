define([
    'jquery',
    'Messages/Application/Events'
], function(
    $,
    Events
) {
    var EventHandler = function(application)
    {
        this.getApplication = function()
        {
            return application;
        };
    };

    EventHandler.prototype.triggerInitialised = function()
    {
        $(document).trigger(Events.INITIALISED);
    };

    return EventHandler;
});