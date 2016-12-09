define([
    'InvoiceDesigner/Template/Element/MapperAbstract',
    'InvoiceDesigner/Template/Element/Box'
], function(
    MapperAbstract,
    BoxElement
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

    Box.prototype.createElement = function()
    {
        return new BoxElement();
    };

    return new Box();
});