define([
    'jquery',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    $,
    domManipulator
) {

    var Barcode = function()
    {
        this.getDomManipulator = function()
        {
            return domManipulator;
        };
    };

    Barcode.prototype.init = function(inspector, element)
    {
        var self = this;
        $('#' + inspector.getBarcodeInspectorActionsId()).off('change').on('change', function(event, container, id) {
            element.setAction(self.getDomManipulator().getValue(this));
        });
    };

    return new Barcode();
});