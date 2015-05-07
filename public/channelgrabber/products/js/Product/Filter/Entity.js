define([
], function () {
    var Entity = function (searchTerm, productId)
    {
        this.getSearchTerm = function()
        {
            return searchTerm;
        };

        this.getProductId = function()
        {
            return productId;
        };
    };

    Entity.prototype.toObject = function()
    {
        return {
            'searchTerm' : this.getSearchTerm(),
            'productId'  : this.getProductId()
        };
    };

    return Entity;
});