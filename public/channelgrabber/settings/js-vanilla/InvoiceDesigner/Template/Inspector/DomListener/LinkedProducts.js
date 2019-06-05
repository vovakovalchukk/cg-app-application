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
        $('#' + inspector.getHeadingInspectorDeleteId()).off('click').on('click', function() {
            inspector.removeElement(template, element);
        });
    };

    return new LinkedProducts();
});