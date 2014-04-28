define(['./AddressAbstract'], function(AddressAbstract)
{
    var SellerAddress = function()
    {
        AddressAbstract.call(this);
    };

    SellerAddress.prototype = Object.create(AddressAbstract.prototype);

    return SellerAddress;
});