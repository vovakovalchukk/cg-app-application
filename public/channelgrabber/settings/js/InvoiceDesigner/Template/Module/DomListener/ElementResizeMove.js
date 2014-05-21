define([
    'InvoiceDesigner/Template/Module/DomListenerAbstract',
    'jquery',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    DomListenerAbstract,
    $,
    domManipulator
) {
    var ElementResizeMove = function()
    {
        DomListenerAbstract.call(this);
    };

    ElementResizeMove.prototype = Object.create(DomListenerAbstract.prototype);

    ElementResizeMove.prototype.init = function(module)
    {
        DomListenerAbstract.prototype.init.call(this, module);
        this.initResizeListener();
        this.initMoveListener();
    };

    ElementResizeMove.prototype.initResizeListener = function()
    {
        var self = this;
        $(document).on(domManipulator.getElementResizedEvent(), function(event, elementDomId, offset, size)
        {
            self.getModule().elementResized(elementDomId, offset, size);
        });
    };

    ElementResizeMove.prototype.initMoveListener = function()
    {
        var self = this;
        $(document).on(domManipulator.getElementMovedEvent(), function(event, elementDomId, offset)
        {
            self.getModule().elementMoved(elementDomId, offset);
        });
    };

    return new ElementResizeMove();
});