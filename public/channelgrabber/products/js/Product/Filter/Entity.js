define([
], function () {
    var Entity = function (searchTerm, parentProductId, id)
    {
        this.getSearchTerm = function()
        {
            return searchTerm;
        };

        this.getParentProductId = function()
        {
            return parentProductId;
        };

        this.getId = function()
        {
            return id;
        };

        this.setId = function(newId)
        {
            id = (newId instanceof Array ? newId : [newId]);
            return this;
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

        var id = this.getId();
        if (id) {
            object['id'] = id;
        }

        return object;
    };

    return Entity;
});