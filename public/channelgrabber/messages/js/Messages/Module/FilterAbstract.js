define([

], function(

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
            active = true;
            return this;
        };

        this.deactivate = function()
        {
            active = false;
            return this;
        };
    };

    /**
     * Get the data that this filter represents when ative
     * @example {status:'new'}
     * @returns Object
     */
    FilterAbstract.prototype.getFilterData = function()
    {
        throw 'Messages/Module/FilterAbstract::getFilterData() should be overridden by subclasses';
    };

    return FilterAbstract;
});
