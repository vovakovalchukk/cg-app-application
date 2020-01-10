define(['InvoiceDesigner/Template/Element/ImmutableTextAbstract'], function(ImmutableTextAbstract)
{
    var DeliveryAddress = function()
    {
        var data = {
            height: 53.5,
            text: "{{b}}{{order.shippingAddressFullName}}\n{{order.shippingAddressCompanyName}}\n{{n}}{{order.shippingAddress1}}{{order.shippingAddress2}}\n{{order.shippingAddress3}}\n{{order.shippingAddressCity}}\n{{order.shippingAddressCounty}}\n{{order.shippingAddressPostcode}}\n{{order.shippingAddressCountry}}"
        };
        ImmutableTextAbstract.call(this, data);
        this.set('type', 'DeliveryAddress', true);
    };

    DeliveryAddress.prototype = Object.create(ImmutableTextAbstract.prototype);

    return DeliveryAddress;
});