define([
    'Messages/Module/FilterCountAbstract'
], function(
    FilterCountAbstract
) {
    var Assignee = function(filterModule, assignee, count)
    {
        FilterCountAbstract.call(this, filterModule, count);

        this.getAssignee = function()
        {
            return assignee;
        };
    };

    Assignee.prototype = Object.create(FilterCountAbstract.prototype);

    Assignee.prototype.getFilterData = function()
    {
        return {
            // We don't pass around the actual userId in the JS as it could easily be changed to someone else's
            assignee: this.getAssignee()
        };
    };

    return Assignee;
});