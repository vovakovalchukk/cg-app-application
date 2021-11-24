define([''], function()
{
    function ShippingMethod()
    {
        const init = function()
        {
            this.listenForShippingMethodChanges();
        };
        init.call(this);
    }

    ShippingMethod.SELECTOR_SHIPPING_METHOD_ID = '#courier-shipping-method-'
    ShippingMethod.SELECTOR_SHIPPING_METHOD = '.courier-shipping-method';
    ShippingMethod.SELECTOR_SHIPPING_METHOD_POPUP_ID = '#courier-shipping-method-popup-'
    ShippingMethod.SELECTOR_SHIPPING_METHOD_POPUP = '.courier-shipping-method-popup';

    ShippingMethod.prototype.listenForShippingMethodChanges = function()
    {
        $(document).on('mouseover', ShippingMethod.SELECTOR_SHIPPING_METHOD, function() {
            let orderId = $(this).attr('orderId');
            $(ShippingMethod.SELECTOR_SHIPPING_METHOD_POPUP_ID+orderId).show();
        }).on('mouseout', ShippingMethod.SELECTOR_SHIPPING_METHOD, function() {
            let orderId = $(this).attr('orderId');
            $(ShippingMethod.SELECTOR_SHIPPING_METHOD_POPUP_ID+orderId).hide();
        });
        return this;
    };

    return ShippingMethod;
});