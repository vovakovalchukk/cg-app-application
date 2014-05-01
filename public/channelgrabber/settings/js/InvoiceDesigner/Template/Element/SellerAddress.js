define(['./ImmutableTextAbstract.js'], function(ImmutableTextAbstract)
{
    var SellerAddress = function()
    {
        ImmutableTextAbstract.call(this);
    };

    SellerAddress.prototype = Object.create(ImmutableTextAbstract.prototype);

    return SellerAddress;
});