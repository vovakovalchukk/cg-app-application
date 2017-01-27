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
        var self = this;
        $('#' + inspector.getFontInspectorFontFamilyId()).off('change').on('change', function(event, selectBox, id) {
            inspector.setFontFamily(element, id);
        });

        $('#' + inspector.getFontInspectorFontSizeId()).off('change').on('change', function(event, selectBox, id) {
            inspector.setFontSize(element, id);
        });

        $('#' + inspector.getFontInspectorAlignId()).off('change').on('change', function(event, align) {
            inspector.setAlign(element, align);
        });

        $('#' + inspector.getFontInspectorFontColourId()).off('change keyup paste').on('change keyup paste', function() {
            inspector.setFontColour(element, self.getDomManipulator().getValue(this));
        });
    };

    return new Font();
});