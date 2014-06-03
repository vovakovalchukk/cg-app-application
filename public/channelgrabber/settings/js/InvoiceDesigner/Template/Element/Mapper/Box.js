define([
    'InvoiceDesigner/Template/Element/MapperAbstract'
], function(
    MapperAbstract
) {
    var Box = function()
    {
        MapperAbstract.call(this);
    };

    Box.prototype = Object.create(MapperAbstract.prototype);

    Box.prototype.getHtmlContents = function(element)
    {
        return '';
    };

    return new Box();
});