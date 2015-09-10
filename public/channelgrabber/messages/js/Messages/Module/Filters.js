define([
    'Messages/Module/Filter/EventHandler',
    'Messages/ModuleAbstract',
    'Messages/Module/Filter/Assignee',
    'Messages/Module/Filter/Status',
    'Messages/Module/Filter/Search',
    'Messages/Module/Filter/Id',
    'Messages/Module/Filter/ExternalUsername',
    'Messages/Headline/Storage/Ajax'
], function(
    EventHandler,
    ModuleAbstract,
    AssigneeFilter,
    StatusFilter,
    SearchFilter,
    IdFilter,
    ExternalUsernameFilter,
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
            filters.myMessages = new AssigneeFilter(this, 'active-user');
            filters.unassigned = new AssigneeFilter(this, 'unassigned');
            filters.assigned = new AssigneeFilter(this, 'assigned');
            filters.resolved = new StatusFilter(this, 'resolved');
            filters.open = new StatusFilter(this, ['new', 'awaiting-reply']);
            filters.search = new SearchFilter(this);
            // 'Hidden' filters
            filters.id = new IdFilter(this, '');
            filters.externalUsername = new ExternalUsernameFilter(this, '');

            this.updateFilterCounts();
        };
        init.call(this);
    };

    Filters.prototype = Object.create(ModuleAbstract.prototype);

    Filters.prototype.initialise = function(selectedThreadId, selectedFilter, selectedFilterValue)
    {
        if (selectedThreadId) {
            selectedFilter = 'id';
            selectedFilterValue = selectedThreadId;
        }
        this.decideFilterAndActivate(selectedFilter, selectedFilterValue);
    };

    Filters.prototype.decideFilterAndActivate = function(selectedFilter, selectedFilterValue)
    {
        var filters = this.getFilters();
        if (selectedFilter && filters[selectedFilter]) {
            var filter = filters[selectedFilter];
            filter.setValue(selectedFilterValue);
            filter.activate();
        } else if (this.getApplication().getSingleUserMode() && filters.open.getCount() > 0) {
            filters.open.activate();
        } else if (filters.myMessages.getCount() > 0) {
            filters.myMessages.activate();
        } else if (filters.unassigned.getCount() > 0) {
            filters.unassigned.activate();
        } else {
            filters.resolved.activate();
        }
    };

    Filters.prototype.applyFilter = function(filter, selectedThreadId)
    {
        // Expected to be picked up by Messages/Module/ThreadList/EventHandler
        this.getEventHandler().triggerApplyRequested(filter, selectedThreadId);
    };

    Filters.prototype.applyActiveFilters = function(selectedThreadId)
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
        this.applyFilter(filter, selectedThreadId);
        return this;
    };

    Filters.prototype.deactivateAll = function(excludeFilter)
    {
        var filters = this.getFilters();
        for (var key in filters) {
            if (!filters[key].isActive() || filters[key] == excludeFilter) {
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
            filters.open.setCount(parseInt(headline.getMyMessages()) + parseInt(headline.getUnassigned()) + parseInt(headline.getAssigned()));
        });
        return this;
    };

    return Filters;
});