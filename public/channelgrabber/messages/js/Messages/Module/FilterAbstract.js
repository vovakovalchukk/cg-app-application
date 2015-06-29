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

        this.isActive = function()
        {
            return active;
        };

        this.activate = function()
        {
            domManipulator.addClass(this.getFilterSelector(), 'active');
            active = true;
            return this;
        };

        this.deactivate = function()
        {
            domManipulator.removeClass(this.getFilterSelector(), 'active');
            active = false;
            return this;
        };
    };

    FilterAbstract.SELECTOR_CONTAINER = '.message-center-nav ul';

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
