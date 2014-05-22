define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/ElementResizeMove',
    'InvoiceDesigner/Template/Element/MapperAbstract',
    'InvoiceDesigner/Template/PaperPage/Mapper',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    ModuleAbstract,
    elementListener,
    ElementMapperAbstract,
    paperPageMapper,
    domManipulator
) {
    var ElementResizeMove = function()
    {
        ModuleAbstract.call(this);
        this.setDomListener(elementListener);

        this.getPaperPageMapper = function()
        {
            return paperPageMapper;
        };

        this.getDomManipulator = function()
        {
            return domManipulator;
        };
    };

    ElementResizeMove.prototype = Object.create(ModuleAbstract.prototype);

    ElementResizeMove.prototype.elementSelected = function(element)
    {
        var elementDomWrapperId = ElementMapperAbstract.getDomWrapperId(element);
        this.getDomManipulator().markAsInactive('.'+ElementMapperAbstract.ELEMENT_DOM_WRAPPER_CLASS);
        this.getDomManipulator().markAsActive('#'+elementDomWrapperId);
    };

    ElementResizeMove.prototype.elementResized = function(elementDomId, offset, size)
    {
        var element = this.getElementByDomId(elementDomId);
        element.setWidth(size.width.pxToMm());
        element.setHeight(size.height.pxToMm());
        this.elementMoved(elementDomId, offset);
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