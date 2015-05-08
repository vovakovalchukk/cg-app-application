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

    Mapper.prototype.fromParentProductId = function(parentProductId)
    {
        var entity = new Entity(
            null,
            parentProductId
        );
        return entity;
    };

    return new Mapper();
});