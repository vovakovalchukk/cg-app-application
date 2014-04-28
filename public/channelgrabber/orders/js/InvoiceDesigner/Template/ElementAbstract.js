define(['../PubSubAbstract'], function(PubSubAbstract) {
    var ElementAbstract = function()
    {
        PubSubAbstract.call(this);
    };

    ElementAbstract.prototype = Object.create(PubSubAbstract.prototype);

    return ElementAbstract;
});