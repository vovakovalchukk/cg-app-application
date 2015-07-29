define([
    'Messages/Thread/Service'
], function(
    service
) {
    var ModuleAbstract = function(application)
    {
        var eventHandler;

        this.getApplication = function()
        {
            return application;
        };

        this.getService = function()
        {
            return service;
        };

        this.getEventHandler = function()
        {
            return eventHandler;
        };

        this.setEventHandler = function(newEventHandler)
        {
            eventHandler = newEventHandler;
            return this;
        };
    };

    return ModuleAbstract;
});