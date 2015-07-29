define([
    'Messages/Module/FilterCountAbstract'
], function(
    FilterCountAbstract
) {
    var Status = function(filterModule, status, count)
    {
        this.getStatus = function()
        {
            return status;
        };

        this.getType = function()
        {
            return status;
        };

        // Must have defined getType() before this as it depends on it
        FilterCountAbstract.call(this, filterModule, count);
    };

    Status.prototype = Object.create(FilterCountAbstract.prototype);

    Status.prototype.getFilterData = function()
    {
        return {
            status: this.getStatus()
        };
    };

    return Status;
});