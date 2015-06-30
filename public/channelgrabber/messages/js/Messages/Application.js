define([
    'Messages/Application//EventHandler',
    // Application Module requires here
    'Messages/Module/Filters',
    'Messages/Module/ThreadList',
    'Messages/Module/ThreadDetails'
], function(
    EventHandler,
    // Application Module variables here
    Filters,
    ThreadList,
    ThreadDetails
) {
    var Application = function(organisationUnitId, assignableUsers)
    {
        var modulesClasses = [
            // Modules here
            Filters,
            ThreadList,
            ThreadDetails
        ];
        var eventHandler;
        var modules = [];

        this.getEventHandler = function()
        {
            return eventHandler;
        };

        this.getModules = function()
        {
            return modules;
        };

        this.getOrganisationUnitId = function()
        {
            return organisationUnitId;
        };

        this.getAssignableUsers = function()
        {
            return assignableUsers;
        };

        var init = function()
        {
            eventHandler = new EventHandler(this);
            for (var key in modulesClasses) {
                var module = new modulesClasses[key](this);
                modules.push(module);
            }
            eventHandler.triggerInitialised();
        };
        init.call(this);
    };

    Application.EVENT_INITIALISED = 'application-initialised';

    return Application;
});