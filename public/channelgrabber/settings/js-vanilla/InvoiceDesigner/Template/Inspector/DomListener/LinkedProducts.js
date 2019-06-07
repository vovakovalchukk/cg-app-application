define([
    'jquery'
], function(
    $
) {
    var LinkedProducts = function() {
    };

    LinkedProducts.prototype.init = function(inspector, template, element, service) {
        let selectId = inspector.getLinkedProductsDisplaySettingSelect();
        $('#' + selectId).off('change').on('change', function(event, selectBox, optionId) {
            inspector.setLinkedProductsDisplay(element, optionId);
        });
    };

    return new LinkedProducts();
});