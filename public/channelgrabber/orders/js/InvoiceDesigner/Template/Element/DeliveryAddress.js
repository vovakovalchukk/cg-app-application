define(['./AddressAbstract'], function(AddressAbstract)
{
    var DeliveryAddress = function()
    {
        AddressAbstract.call(this);
    };

    DeliveryAddress.prototype = Object.create(AddressAbstract.prototype);

    return DeliveryAddress;
});