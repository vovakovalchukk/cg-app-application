define([
    'Messages/Module/FilterAbstract',
    'cg-mustache',
    'DomManipulator'
], function(
    FilterAbstract,
    CGMustache,
    domManipulator
) {
    var Search = function(filterModule)
    {
        var type = 'search';

        this.getDomManipulator = function()
        {
            return domManipulator;
        };

        this.getType = function()
        {
            return type;
        };

        // Must have defined getType() before this as it depends on it
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
