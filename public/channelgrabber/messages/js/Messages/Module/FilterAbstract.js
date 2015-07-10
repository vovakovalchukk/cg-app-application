define([
    'DomManipulator'
], function(
    domManipulator
) {
    var FilterAbstract = function(filterModule)
    {
        var active = false;

        this.getFilterModule = function()
        {
            return filterModule;
        };

        this.setFilterModule = function(newFilterModule)
        {
            filterModule = newFilterModule;
            return this;
        };

        this.getDomManipulator = function()
        {
            return domManipulator;
        };

        this.isActive = function()
        {
            return active;
        };

        // Don't call this directly, use activate() and deactivate()
        this.setActive = function(isActive)
        {
            active = isActive;
            return this;
        };
    };

    FilterAbstract.SELECTOR_CONTAINER = '.message-center-nav ul';

    FilterAbstract.prototype.activate = function()
    {
        this.getFilterModule().deactivateAll();
        this.getDomManipulator().addClass(this.getFilterSelector(), 'active');
        this.setActive(true);
        this.getFilterModule().applyActiveFilters();
        return this;
    };

    FilterAbstract.prototype.deactivate = function()
    {
        this.getDomManipulator().removeClass(this.getFilterSelector(), 'active');
        this.setActive(false);
        return this;
    };

    FilterAbstract.prototype.getFilterSelector = function()
    {
        return FilterAbstract.SELECTOR_CONTAINER + ' li[data-filter-type="' + this.getType() + '"]';
    };

    /**
     * Get the data that this filter represents when active
     * @example {status:'new'}
     * @returns Object
     */
    FilterAbstract.prototype.getFilterData = function()
    {
        throw 'Messages/Module/FilterAbstract::getFilterData() should be overridden by subclasses';
    };

    /**
     * Get the type of this filter
     * @example 'mymessages'
     * @returns String
     */
    FilterAbstract.prototype.getType = function()
    {
        throw 'Messages/Module/FilterAbstract::getType() should be overridden by subclasses';
    };

    return FilterAbstract;
});
