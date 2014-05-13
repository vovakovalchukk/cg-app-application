define(['InvoiceDesigner/Template/Element/ImmutableTextAbstract'], function(ImmutableTextAbstract)
{
    var SellerAddress = function()
    {
        ImmutableTextAbstract.call(this);
    };

    SellerAddress.prototype = Object.create(ImmutableTextAbstract.prototype);

    return new SellerAddress();
});