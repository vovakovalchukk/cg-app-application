define([
], function () {
    var Entity = function (searchTerm, parentProductId)
    {
        this.getSearchTerm = function()
        {
            return searchTerm;
        };

        this.getParentProductId = function()
        {
            return parentProductId;
        };
    };

    Entity.prototype.toObject = function()
    {
        var object = {};

        var searchTerm = this.getSearchTerm();
        if (searchTerm) {
            object['searchTerm'] = searchTerm;
        }

        var parentProductId = this.getParentProductId();
        if (parentProductId) {
            object['parentProductId'] = [parentProductId];
        }

        return object;
    };

    return Entity;
});