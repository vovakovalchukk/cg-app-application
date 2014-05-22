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
        $('#' + inspector.getFontInspectorFontFamilyId()).off('change').on('change', function(event, selectBox, id) {
            element.setFontFamily(id);
        });

        $('#' + inspector.getFontInspectorFontSizeId()).off('change').on('change', function(event, selectBox, id) {
            element.setFontSize(id);
        });

        $('#' + inspector.getFontInspectorAlignId()).off('change').on('change', function(event, align) {
            element.setAlign(align);
        });

        $('#' + inspector.getFontInspectorFontColourId()).off('change keyup paste').on('change keyup paste', function() {
            element.setFontColour(that.getDomManipulator().getValue(this));
        });
    };

    return new Font();
});