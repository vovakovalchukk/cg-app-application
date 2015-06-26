define([
    'Messages/Module/Filter/EventHandler',
    'Messages/ModuleAbstract',
    'Messages/Module/Filter/Assignee',
    'Messages/Module/Filter/Status',
    'Messages/Module/Filter/Search'
], function(
    EventHandler,
    ModuleAbstract,
    AssigneeFilter,
    StatusFilter,
    SearchFilter
) {
    var Filters = function(application)
    {
        ModuleAbstract.call(this, application);

        var filters = [];

        this.getFilters = function()
        {
            return filters;
        };

        var init = function()
        {
            this.setEventHandler(new EventHandler(this));
            // TODO: CGIV-5839, get these counts from somewhere
            var myMessagesCount = 0;
            var unassignedCount = 0;
            var assignedCount = 0;
            var resolvedCount = 0;
            // We don't want to use the actual userId here as this is client-side and it could be changed maliciously
            var myMessages = new AssigneeFilter(this, 'active-user', myMessagesCount);
            myMessages.activate();
            filters.push(myMessages);
            filters.push(new AssigneeFilter(this, 'unassigned', unassignedCount));
            filters.push(new AssigneeFilter(this, 'assigned', assignedCount));
            filters.push(new StatusFilter(this, 'resolved', resolvedCount));
            filters.push(new SearchFilter(this));
        };
        init.call(this);
    };

    Filters.prototype = Object.create(ModuleAbstract.prototype);

    Filters.prototype.applyFilter = function(filter)
    {
        // Expected to be picked up by Messages/Module/ThreadList/EventHandler
        this.getEventHandler().triggerApplyRequested(filter);
    };

    Filters.prototype.applyActiveFilters = function()
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
        this.applyFilter(filter);
    };

    return Filters;
});