define([
    'Messages/Module/FilterAbstract'
], function(
    FilterAbstract
) {
    var Id = function(filterModule, id)
    {
        var type = 'id';

        this.getId = function()
        {
            return id;
        };

        this.setId = function(newId)
        {
            id = newId;
            return this;
        };

        this.getType = function()
        {
            return type;
        };

        // Must have defined getType() before this as it depends on it
        FilterAbstract.call(this, filterModule);
    };

    Id.prototype = Object.create(FilterAbstract.prototype);

    Id.prototype.getFilterData = function()
    {
        return {
            id: this.getId()
        };
    };

    Id.prototype.setValue = function(value)
    {
        this.setId(value);
        return this;
    };

    // Overridden to pass the ID along to applyActiveFilters()
    Id.prototype.activate = function()
    {
        this.getFilterModule().deactivateAll(this);
        this.getDomManipulator().addClass(this.getFilterSelector(), 'active');
        this.setActive(true);
        this.getFilterModule().applyActiveFilters(this.getId());
        return this;
    };

    return Id;
});