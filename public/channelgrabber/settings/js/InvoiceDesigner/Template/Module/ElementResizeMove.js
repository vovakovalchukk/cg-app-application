define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/ElementResizeMove',
    'InvoiceDesigner/Template/Element/MapperAbstract',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    ModuleAbstract,
    elementListener,
    ElementMapperAbstract,
    domManipulator
) {
    var ElementResizeMove = function()
    {
        ModuleAbstract.call(this);
        this.setDomListener(elementListener);

        this.getDomManipulator = function()
        {
            return domManipulator;
        };
    };

    ElementResizeMove.prototype = Object.create(ModuleAbstract.prototype);

    ElementResizeMove.prototype.elementSelected = function(element)
    {
        var elementDomId = ElementMapperAbstract.getDomId(element);
        this.getDomManipulator().markAsInactive('.'+ElementMapperAbstract.ELEMENT_DOM_CLASS);
        this.getDomManipulator().markAsActive('#'+elementDomId);
    };

    ElementResizeMove.prototype.elementResized = function(elementDomId, position, size)
    {
        var element = this.getElementByDomId(elementDomId);
        element.setWidth(size.width.pxToMm());
        element.setHeight(size.height.pxToMm());
        this.elementMoved(elementDomId, position);
    };

    ElementResizeMove.prototype.elementMoved = function(elementDomId, position)
    {
        var element = this.getElementByDomId(elementDomId);
        element.setX(position.left.pxToMm());
        element.setY(position.top.pxToMm());
    };

    ElementResizeMove.prototype.getElementByDomId = function(elementDomId)
    {
        var elementId = ElementMapperAbstract.getElementIdFromDomId(elementDomId);
        var element = this.getTemplate().getElements().getById(elementId);
        return element;
    };

    return new ElementResizeMove();
});