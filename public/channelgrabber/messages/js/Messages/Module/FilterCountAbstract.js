define([
    'Messages/Module/FilterAbstract'
], function(
    FilterAbstract
) {
    var FilterCountAbstract = function(filterModule, count)
    {
        FilterAbstract.call(this, filterModule);

        this.getCount = function()
        {
            return count;
        };
    };

    FilterCountAbstract.prototype = Object.create(FilterAbstract.prototype);

    return FilterCountAbstract;
});
