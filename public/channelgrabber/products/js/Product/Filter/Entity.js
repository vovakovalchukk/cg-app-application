define([
], function () {
    var Entity = function (searchTerm)
    {
        this.getSearchTerm = function()
        {
            return searchTerm;
        };
    };

    Entity.prototype.toJson = function()
    {
        return JSON.stringify({
            'searchTerm' : this.getSearchTerm()
        });
    };

    return Entity;
});