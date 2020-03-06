define([
    'InvoiceDesigner/Template/Element/Mapper/TextAbstract',
    'InvoiceDesigner/Template/Element/Text'
], function(
    TextAbstract,
    TextElement
) {
    var DeliveryAddress = function()
    {
        TextAbstract.call(this);
    };

    DeliveryAddress.prototype = Object.create(TextAbstract.prototype);

    DeliveryAddress.prototype.createElement = function()
    {
        var element = new TextElement();
        var text = "{{order.shippingAddressFullName}}\n{{order.shippingAddressCompanyName}}\n{{order.shippingAddress1}}\n{{order.shippingAddress2}}\n{{order.shippingAddress3}}\n{{order.shippingAddressCity}}\n{{order.shippingAddressCounty}}\n{{order.shippingAddressPostcode}}";
        return element
            .setWidth('100')
            .setHeight('40')
            .setText(text)
            .setRemoveBlankLines(true);
    };

    return new DeliveryAddress();
});