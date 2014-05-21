define([
    'jquery',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    $,
    domManipulator
    ) {

    var Font = function()
    {
        this.getDomManipulator = function()
        {
            return domManipulator;
        };
    };

    Font.prototype.init = function(inspector, element)
    {
        var that = this;
    };

    return new Font();
});