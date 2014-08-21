define([
], function () {
    var Entity = function (searchTerm)
    {
        this.getSearchTerm = function()
        {
            return searchTerm;
        };
    };

    Entity.prototype.toArray = function()
    {
        return {
            'searchTerm' : this.getSearchTerm()
        };
    };

    return Entity;
});