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
            inspector.setBorderWidth(element, id);
        });

        $('#' + inspector.getBorderInspectorBorderColourId()).off('change keyup paste').on('change keyup paste', function() {
            inspector.setBorderColour(element, that.getDomManipulator().getValue(this));
        });
    };

    return new Border();
});

