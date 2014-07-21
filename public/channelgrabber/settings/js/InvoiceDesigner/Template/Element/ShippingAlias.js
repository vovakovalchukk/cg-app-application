define(['InvoiceDesigner/Template/Element/ImmutableTextAbstract'], function(ImmutableTextAbstract)
{
    var ShippingAlias = function()
    {
        var data = {
            height: 15,
            text: "%%b%%SHIPPING METHOD:\n%%n%%%%order.shippingAlias%%"
        };
        ImmutableTextAbstract.call(this, data);
        this.set('type', 'ShippingAlias', true);
    };

    ShippingAlias.prototype = Object.create(ImmutableTextAbstract.prototype);

    return ShippingAlias;
});