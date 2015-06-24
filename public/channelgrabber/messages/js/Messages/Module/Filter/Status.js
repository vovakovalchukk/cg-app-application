define([
    'Messages/Module/FilterCountAbstract'
], function(
    FilterCountAbstract
) {
    var Status = function(filterModule, status, count)
    {
        FilterCountAbstract.call(this, filterModule, count);

        this.getStatus = function()
        {
            return status;
        };
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