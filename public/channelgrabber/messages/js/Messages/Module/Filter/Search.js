define([
    'Messages/Module/FilterAbstract',
    'Messages/Module/Filter/Search/EventHandler',
    'cg-mustache',
    'DomManipulator'
], function(
    FilterAbstract,
    EventHandler,
    CGMustache,
    domManipulator
) {
    var Search = function(filterModule)
    {
        var eventHandler;
        var type = 'search';

        this.getDomManipulator = function()
        {
            return domManipulator;
        };

        this.getEventHandler = function()
        {
            return eventHandler;
        };

        this.getType = function()
        {
            return type;
        };

        // Must have defined getType() before this as it depends on it
        FilterAbstract.call(this, filterModule);

        var init = function()
        {
            eventHandler = new EventHandler(this);
        };
        init.call(this);
    };

    Search.SELECTOR_INPUT = '#filter-search-field';

    Search.prototype = Object.create(FilterAbstract.prototype);
    
    Search.prototype.getFilterData = function()
    {
        return {
            searchTerm: this.getSearchTerm()
        };
    };

    Search.prototype.getSearchTerm = function()
    {
        return this.getDomManipulator().getValue(Search.SELECTOR_INPUT);
    };

    Search.prototype.activate = function()
    {
        if (this.getSearchTerm().trim() == '') {
            return;
        }

        // parent::activate()
        FilterAbstract.prototype.activate.call(this);
    };

    Search.prototype.deactivate = function()
    {
        // parent::deactivate()
        FilterAbstract.prototype.deactivate.call(this);
        this.resetSearchTerm();
    };

    Search.prototype.resetSearchTerm = function()
    {
        this.getDomManipulator().setValue(Search.SELECTOR_INPUT, '');
    };

    return Search;
});
