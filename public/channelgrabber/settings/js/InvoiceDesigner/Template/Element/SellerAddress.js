define(['InvoiceDesigner/Template/Element/ImmutableTextAbstract'], function(ImmutableTextAbstract)
{
    var SellerAddress = function()
    {
        ImmutableTextAbstract.call(this);
        this.setType('SellerAddress');
    };

    SellerAddress.prototype = Object.create(ImmutableTextAbstract.prototype);

    return SellerAddress;
});