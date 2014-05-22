define([
    'jquery',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    $,
    domManipulator
) {

    var Border = function()
    {
        this.getDomManipulator = function()
        {
            return domManipulator;
        };
    };

    Border.prototype.init = function(inspector, element)
    {
        var that = this;
        $('#' + inspector.getBorderInspectorBorderWidthId()).off('change').on('change', function(event, selectBox, id) {
            element.setBorderWidth(id);
        });

        $('#' + inspector.getBorderInspectorBorderColourId()).off('change keyup paste').on('change keyup paste', function() {
            element.setBorderColour(that.getDomManipulator().getValue(this));
        });
    };

    return new Border();
});