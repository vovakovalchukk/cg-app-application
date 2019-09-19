define([
    'jquery',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    $,
    domManipulator
) {
    const TableCellsDomListener = function()
    {
        this.getDomManipulator = function()
        {
            return domManipulator;
        };
    };

    TableCellsDomListener.prototype.init = function(inspector, element)
    {
        $('#' + inspector.FONT_FAMILY_ID).off('change').on('change', (event, selectBox, id) => {
            console.log('on change');
            inspector.setFontFamily(element, id);
        });

        $('#' + inspector.FONT_SIZE_ID).off('change').on('change', (event, selectBox, id) => {
            inspector.setFontSize(element, id);
        });

        $('#' + inspector.FONT_ALIGN_ID).off('change').on('change', (event, align) => {
            inspector.setAlign(element, align);
        });

        $('#' + inspector.FONT_COLOR_ID).off('change keyup paste').on('change keyup paste', () => {
            inspector.setFontColour(element, this.getDomManipulator().getValue(this));
        });
    };

    return new TableCellsDomListener();
});