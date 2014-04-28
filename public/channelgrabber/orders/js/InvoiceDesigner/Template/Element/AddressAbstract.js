define(['./TextAbstract'], function(TextAbstract)
{
    var AddressAbstract = function()
    {
        TextAbstract.call(this);

        this.setEditable(false);
    };

    AddressAbstract.prototype = Object.create(TextAbstract.prototype);

    return AddressAbstract;
});