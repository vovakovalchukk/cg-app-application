define(['./ImmutableTextAbstract.js'], function(ImmutableTextAbstract)
{
    var DeliveryAddress = function()
    {
        ImmutableTextAbstract.call(this);
    };

    DeliveryAddress.prototype = Object.create(ImmutableTextAbstract.prototype);

    return DeliveryAddress;
});