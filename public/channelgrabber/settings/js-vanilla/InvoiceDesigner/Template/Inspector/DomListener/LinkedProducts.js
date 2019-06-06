define([
    'jquery'
], function(
    $
) {

    var LinkedProducts = function()
    {
    };

    LinkedProducts.prototype.init = function(inspector, template, element, service)
    {
//        debugger;
        console.log('inspector: ', inspector);
        let selectId = inspector.getLinkedProductsDisplaySettingSelect();
        let selectElement = $('#' + selectId);
        selectElement.off('change').on('change', function(event, selectBox, optionId) {
            console.log('event in change: ', event);
            debugger;

            // ID is the desired value
            element.setLinkedProductsDisplay(optionId);
        });



    };

    return new LinkedProducts();
});