define([
], function () {
    var Entity = function (searchTerm, parentProductId, id)
    {
        var page = 1;

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

        this.getPage = function()
        {
            return page;
        };

        this.setPage = function(newPage)
        {
            page = newPage;
            return this;
        };
    };

    Entity.prototype.toObject = function()
    {
        var object = {
            "page": this.getPage()
        };

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