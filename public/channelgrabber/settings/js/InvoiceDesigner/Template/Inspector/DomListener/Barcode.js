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
        $('#' + inspector.getBarcodeInspectorActionsId()).off('change').on('change', function(event, container, id) {
            element.setAction(id);
        });
    };

    return new Barcode();
});