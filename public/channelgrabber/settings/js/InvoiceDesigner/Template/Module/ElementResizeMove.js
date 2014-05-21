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

    ElementResizeMove.prototype.elementResized = function(elementDomId, position, size)
    {
        var element = this.getElementByDomId(elementDomId);
        element.setWidth(this.pxToMm(size.width));
        element.setHeight(this.pxToMm(size.height));
        this.elementMoved(elementDomId, position);
    };

    ElementResizeMove.prototype.elementMoved = function(elementDomId, position)
    {
        var element = this.getElementByDomId(elementDomId);
        element.setX(this.pxToMm(position.left));
        element.setY(this.pxToMm(position.top));
    };

    ElementResizeMove.prototype.getElementByDomId = function(elementDomId)
    {
        var elementId = ElementMapperAbstract.getElementIdFromDomId(elementDomId);
        var element = this.getTemplate().getElements().getById(elementId);
        return element;
    };

    ElementResizeMove.prototype.pxToMm = function(px)
    {
        var pxPerMm = this.getDomManipulator().getPxPerMm();
        return px / pxPerMm;
    };

    return new ElementResizeMove();
});