define([
    'ShippingAlias/DomManipulator'
],
function(domManipulator)
{
    var ShippingMethod = function() { };

    ShippingMethod.SHIPPING_METHOD_SELECTOR = '#shipping-alias-container .custom-select-item';

    ShippingMethod.prototype.init = function(module)
    {
        var self = this;

        $(ShippingMethod.SHIPPING_METHOD_SELECTOR).click(function () {
            domManipulator.prependAlias();
        });
    };

    return new ShippingMethod();
});