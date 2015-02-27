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
        $('#' + inspector.getShowVatId()).off('change').on('change', function(event, container, id) {
            var isSelected = $('#' + inspector.getShowVatId()).is(":checked");
            element.setShowVat(isSelected);
        });
    };

    return new OrderTableOptions();
});

