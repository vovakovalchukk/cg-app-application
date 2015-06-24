define([
    'Messages/ModuleAbstract',
    'Messages/Module/Filter/Assignee',
    'Messages/Module/Filter/Status',
    'Messages/Module/Filter/Search'
], function(
    ModuleAbstract,
    AssigneeFilter,
    StatusFilter,
    SearchFilter
) {
    var Filters = function(application)
    {
        ModuleAbstract.call(this, application);

        var filters = [];

        var init = function()
        {
            // TODO: get the counts from somewhere
            var myMessagesCount = 0;
            var unassignedCount = 0;
            var assignedCount = 0;
            var resolvedCount = 0;
            filters.push(new AssigneeFilter(this, 'active-user', myMessagesCount));
            filters.push(new AssigneeFilter(this, 'unassigned', unassignedCount));
            filters.push(new AssigneeFilter(this, 'assigned', assignedCount));
            filters.push(new StatusFilter(this, 'resolved', resolvedCount));
            filters.push(new SearchFilter(this));

console.log('Filters initialised');
        };
        init.call(this);
    };

    Filters.prototype = Object.create(ModuleAbstract.prototype);

    Filters.prototype.applyFilter = function(filter)
    {
        
    };

    return Filters;
});