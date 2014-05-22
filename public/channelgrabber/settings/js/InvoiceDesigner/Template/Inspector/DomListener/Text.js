define([
    'jquery',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    $,
    domManipulator
    ) {

    var Text = function()
    {
        this.getDomManipulator = function()
        {
            return domManipulator;
        };
    };

    Text.prototype.init = function(inspector, element)
    {
        var self = this;
    };

    return new Text();
});