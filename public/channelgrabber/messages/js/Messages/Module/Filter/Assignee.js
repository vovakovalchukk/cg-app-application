define([
    'Messages/Module/FilterCountAbstract'
], function(
    FilterCountAbstract
) {
    var Assignee = function(filterModule, assignee, count)
    {
        this.getAssignee = function()
        {
            return assignee;
        };

        this.getType = function()
        {
            return assignee;
        };

        // Must have defined getType() before this as it depends on it
        FilterCountAbstract.call(this, filterModule, count);
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