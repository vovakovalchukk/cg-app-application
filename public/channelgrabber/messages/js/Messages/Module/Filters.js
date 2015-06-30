define([
    'Messages/Module/Filter/EventHandler',
    'Messages/ModuleAbstract',
    'Messages/Module/Filter/Assignee',
    'Messages/Module/Filter/Status',
    'Messages/Module/Filter/Search',
    'Messages/Headline/Storage/Ajax'
], function(
    EventHandler,
    ModuleAbstract,
    AssigneeFilter,
    StatusFilter,
    SearchFilter,
    headlineStorage
) {
    var Filters = function(application)
    {
        ModuleAbstract.call(this, application);

        var filters = {};

        this.getFilters = function()
        {
            return filters;
        };

        this.getHeadlineStorage = function()
        {
            return headlineStorage;
        };

        var init = function()
        {
            this.setEventHandler(new EventHandler(this));
            // We don't want to use the actual userId here as this is client-side and it could be changed maliciously
            var myMessages = new AssigneeFilter(this, 'active-user');
            myMessages.activate();
            filters.myMessages = myMessages;
            filters.unassigned = new AssigneeFilter(this, 'unassigned');
            filters.assigned = new AssigneeFilter(this, 'assigned');
            filters.resolved = new StatusFilter(this, 'resolved');
            filters.search = new SearchFilter(this);
        };
        init.call(this);
    };

    Filters.prototype = Object.create(ModuleAbstract.prototype);

    Filters.prototype.applyFilter = function(filter, selectedThread)
    {
        // Expected to be picked up by Messages/Module/ThreadList/EventHandler
        this.getEventHandler().triggerApplyRequested(filter, selectedThread);
    };

    Filters.prototype.applyActiveFilters = function(selectedThread)
    {
        var filter = {};
        var filters = this.getFilters();
        for (var key in filters) {
            if (!filters[key].isActive()) {
                continue;
            }
            var filterData = filters[key].getFilterData();
            for (var key2 in filterData) {
                filter[key2] = filterData[key2];
            }
        }
        this.applyFilter(filter, selectedThread);
        return this;
    };

    Filters.prototype.deactivateAll = function()
    {
        var filters = this.getFilters();
        for (var key in filters) {
            if (!filters[key].isActive()) {
                continue;
            }
            filters[key].deactivate();
        }
        return this;
    };

    Filters.prototype.updateFilterCounts = function()
    {
        var self = this;
        this.getHeadlineStorage().fetch(this.getApplication().getOrganisationUnitId(), function(headline)
        {
            var filters = self.getFilters();
            filters.myMessages.setCount(headline.getMyMessages());
            filters.unassigned.setCount(headline.getUnassigned());
            filters.assigned.setCount(headline.getAssigned());
            filters.resolved.setCount(headline.getResolved());
        });
        return this;
    };

    return Filters;
});