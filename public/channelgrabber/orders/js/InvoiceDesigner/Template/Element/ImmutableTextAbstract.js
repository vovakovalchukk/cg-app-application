define(['./TextAbstract'], function(TextAbstract)
{
    var ImmutableTextAbstract = function()
    {
        TextAbstract.call(this);

        this.setEditable(false);
    };

    ImmutableTextAbstract.prototype = Object.create(TextAbstract.prototype);

    return ImmutableTextAbstract;
});