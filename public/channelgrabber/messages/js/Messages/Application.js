define([
    'Messages/Application//EventHandler',
    'DomManipulator',
    // Application Module requires here
    'Messages/Module/Filters',
    'Messages/Module/ThreadList',
    'Messages/Module/ThreadDetails'
], function(
    EventHandler,
    domManipulator,
    // Application Module variables here
    Filters,
    ThreadList,
    ThreadDetails
) {
    var Application = function(
        uri,
        organisationUnitId,
        assignableUsers,
        singleUserMode,
        selectedThreadId,
        selectedFilter,
        selectedFilterValue
    ) {
        var modulesClasses = [
            // Modules here
            Filters,
            ThreadList,
            ThreadDetails
        ];
        var eventHandler;
        var modules = [];
        var busyCount = 0;

        this.getEventHandler = function()
        {
            return eventHandler;
        };

        this.getDomManipulator = function()
        {
            return domManipulator;
        };

        this.getModules = function()
        {
            return modules;
        };

        this.getBusyCount = function()
        {
            return busyCount;
        };

        this.setBusyCount = function(newBusyCount)
        {
            busyCount = newBusyCount;
            return this;
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

        this.getSingleUserMode = function()
        {
            return singleUserMode;
        };

        this.getSelectedFilter = function()
        {
            return selectedFilter;
        };

        this.getSelectedFilterValue = function()
        {
            return selectedFilterValue;
        };

        var init = function()
        {
            eventHandler = new EventHandler(this);
            for (var key in modulesClasses) {
                var module = new modulesClasses[key](this);
                modules.push(module);
            }

            // Tell any listeners that we're ready. Expected to be picked up by Module\Filter\EventHandler
            eventHandler.triggerInitialised(
                this.getSelectedThreadId(), this.getSelectedFilter(), this.getSelectedFilterValue()
            );
        };
        init.call(this);
    };

    Application.SELECTOR_LOADING = '#threads-loading-message';

    Application.prototype.setUrlForThread = function(thread)
    {
        window.history.pushState({}, window.document.title, this.getUri() + '/' + thread.getId());
    };

    Application.prototype.busy = function()
    {
        var currentlyBusy = this.getBusyCount();
        var nowBusy = currentlyBusy + 1;
        this.setBusyCount(nowBusy);
        if (nowBusy > 1) {
            return;
        }
        this.getDomManipulator().show(Application.SELECTOR_LOADING);
    };

    Application.prototype.unbusy = function()
    {
        var currentlyBusy = this.getBusyCount();
        var nowBusy = currentlyBusy - 1;
        this.setBusyCount(nowBusy);
        if (nowBusy > 0) {
            return;
        }
        this.getDomManipulator().hide(Application.SELECTOR_LOADING);
    };

    return Application;
});