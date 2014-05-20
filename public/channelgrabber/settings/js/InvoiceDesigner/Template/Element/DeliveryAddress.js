define(['InvoiceDesigner/Template/Element/ImmutableTextAbstract'], function(ImmutableTextAbstract)
{
    var DeliveryAddress = function()
    {
        ImmutableTextAbstract.call(this);
        this.setType('DeliveryAddress');
    };

    DeliveryAddress.prototype = Object.create(ImmutableTextAbstract.prototype);

    return DeliveryAddress;
});