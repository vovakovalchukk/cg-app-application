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
        $('#' + inspector.getFontInspectorFontColourId()).off('change keyup paste').on('change keyup paste', function() {
            console.log($(this).val());
            element.setFontColour(that.getDomManipulator().getValue(this));
            console.log(element.getFontColour());
        });
    };

    return new Font();
});