define([
    'InvoiceDesigner/Template/Element/Mapper/TextAbstract'
], function(
    TextAbstract
) {
    var ShippingAlias = function()
    {
        TextAbstract.call(this);
    };

    ShippingAlias.prototype = Object.create(TextAbstract.prototype);

    return new ShippingAlias();
});