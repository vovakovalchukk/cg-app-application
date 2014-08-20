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
            elementCollection.get('searchTerm').getValue()
        );
        return entity;
    };

    return new Mapper();
});