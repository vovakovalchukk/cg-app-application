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
    var Application = function(uri, organisationUnitId, assignableUsers, selectedThreadId)
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

        this.getUri = function()
        {
            return uri;
        };

        this.getOrganisationUnitId = function()
        {
            return organisationUnitId;
        };

        this.getAssignableUsers = function()
        {
            return assignableUsers;
        };

        this.getSelectedThreadId = function()
        {
            return selectedThreadId;
        };

        var init = function()
        {
            eventHandler = new EventHandler(this);
            for (var key in modulesClasses) {
                var module = new modulesClasses[key](this);
                modules.push(module);
            }

            // Tell any listeners that we're ready. Expected to be picked up by Module\Filter\EventHandler
            eventHandler.triggerInitialised(this.getSelectedThreadId());
        };
        init.call(this);
    };

    Application.prototype.setUrlForThread = function(thread)
    {
        window.history.pushState({}, window.document.title, this.getUri() + '/' + thread.getId());
    };

    return Application;
});