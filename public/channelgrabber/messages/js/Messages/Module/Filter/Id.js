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

    return Id;
});