define(['InvoiceDesigner/Template/DomManipulator'], function(domManipulator)
{
    var Service = function()
    {
        this.getDomManipulator = function()
        {
            return domManipulator;
        };
    };

    Service.ELEMENT_DOM_WRAPPER_CLASS = 'template-element-wrapper';
    Service.element_dom_wrapper_gap = undefined;

    Service.prototype.getElementDomWrapperGap = function()
    {
        if (!Service.element_dom_wrapper_gap) {
            var wrapperDimensions = this.getDomManipulator().getDimensionsOfTemporaryElement(Service.ELEMENT_DOM_WRAPPER_CLASS);
            var pixelGap = (wrapperDimensions.outerWidth - wrapperDimensions.width) / 2;
            Service.element_dom_wrapper_gap = pixelGap.pxToMm();
        }

        return Service.element_dom_wrapper_gap;
    };

    Service.prototype.addDomWrapperGapToDimensions = function(dimensions)
    {
        return this.adjustDimensionsForDomWrapperGap(dimensions, true);
    };

    Service.prototype.removeDomWrapperGapFromDimensions = function(dimensions)
    {
        return this.adjustDimensionsForDomWrapperGap(dimensions, false);
    };

    Service.prototype.adjustDimensionsForDomWrapperGap = function(dimensions, addGap)
    {
        var gap = this.getElementDomWrapperGap();
        for (var dimension in dimensions) {
            if (addGap) {
                dimensions[dimension] += gap;
            } else {
                dimensions[dimension] -= gap;
            }
        }
        return dimensions;
    };

    Service.prototype.getElementDomWrapperClass = function()
    {
        return Service.ELEMENT_DOM_WRAPPER_CLASS;
    };

    return new Service();
});