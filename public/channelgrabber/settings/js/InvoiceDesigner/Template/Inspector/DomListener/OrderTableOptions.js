define([
    'jquery',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    $,
    domManipulator
) {

    var OrderTableOptions = function()
    {
        this.getDomManipulator = function()
        {
            return domManipulator;
        };
    };

    OrderTableOptions.prototype.init = function(inspector, element)
    {
        var that = this;
        $('#' + inspector.getShowVatId()).off('change').on('change', function(event, container, id) {
            var isSelected = $('#' + inspector.getShowVatId()).is(":checked");
            element.setRemoveBlankLines(isSelected);
        });
    };

    return new OrderTableOptions();
});

