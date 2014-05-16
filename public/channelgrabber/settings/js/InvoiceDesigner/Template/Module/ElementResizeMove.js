define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/ElementResizeMove',
    'InvoiceDesigner/Template/Element/MapperAbstract'
], function(
    ModuleAbstract,
    elementListener,
    ElementMapperAbstract
) {
    var ElementResizeMove = function()
    {
        ModuleAbstract.call(this);
        this.setDomListener(elementListener);
    };

    ElementResizeMove.prototype = Object.create(ModuleAbstract.prototype);

    ElementResizeMove.prototype.elementResized = function(elementDomId, size)
    {
        var element = this.getElementByDomId(elementDomId);
        element.setWidth(size.width);
        element.setHeight(size.height);
    };

    ElementResizeMove.prototype.elementMoved = function(elementDomId, offset)
    {
        var element = this.getElementByDomId(elementDomId);
        element.setX(offset.left);
        element.setY(offset.top);
    };

    ElementResizeMove.prototype.getElementByDomId = function(elementDomId)
    {
        var elementId = ElementMapperAbstract.getElementIdFromDomId(elementDomId);
        var element = this.getTemplate().getElements().getById(elementId);
        return element;
    };

    return new ElementResizeMove();
});