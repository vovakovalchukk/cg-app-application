define([
    'Product/Filter/Entity',
    'element/ElementCollection'
], function (
    Entity,
    elementCollection
) {
    var Mapper = function ()
    {
    };

    Mapper.prototype.fromDom = function()
    {
        var entity = new Entity(
            elementCollection.get('searchTerm').getValue(),
            null
        );
        return entity;
    };

    Mapper.prototype.fromProductId = function(productId)
    {
        var entity = new Entity(
            null,
            productId
        );
        return entity;
    };

    return new Mapper();
});