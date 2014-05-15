define([
    'InvoiceDesigner/Template/Element/Mapper/TextAbstract'
], function(
    TextAbstract
) {
    var DeliveryAddress = function()
    {
        TextAbstract.call(this);
    };

    DeliveryAddress.prototype = Object.create(TextAbstract.prototype);

    return new DeliveryAddress();
});