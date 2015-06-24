define([
    'Messages/Module/FilterAbstract'
], function(
    FilterAbstract
) {
    var Search = function(filterModule)
    {
        FilterAbstract.call(this, filterModule);
    };

    Search.prototype = Object.create(FilterAbstract.prototype);

    Search.prototype.getFilterData = function()
    {
        return {
            searchTerm: this.getSearchTerm()
        };
    };

    Search.prototype.getSearchTerm = function()
    {
        // Get from the underlying input box
    };

    return Search;
});
