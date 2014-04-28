define(function() {
    var ElementAbstract = function()
    {
        var subscribers = [];

        this.getSubscribers = function()
        {
            return subscribers;
        };
    };

    ElementAbstract.PUBLISH_METHOD = 'elementUpdate';

    ElementAbstract.prototype.subscribe = function(subscriber)
    {
        if (!subscriber.hasMethods([ElementAbstract.PUBLISH_METHOD, 'getId'])) {
            throw 'InvalidArgumentException: InvoiceDesigner\Template\Element::subscribe() '+
                'must be passed a subscriber with a ' + ElementAbstract.PUBLISH_METHOD + ' method';
        }

        this.getSubscribers().push(subscriber);
        return this;
    };

    ElementAbstract.prototype.unsubscribe = function(subscriber)
    {
        var subscribers = this.getSubscribers();
        for (var key in subscribers) {
            if (subscribers[key].getId() == subscriber.getId()) {
                this.getSubscribers().splice(key, 1);
                break;
            }
        }
        return this;
    };

    ElementAbstract.prototype.publish = function()
    {
        var subscribers = this.getSubscribers();
        for (var key in subscribers) {
            subscribers[key][ElementAbstract.PUBLISH_METHOD](this);
        }
        return this;
    };

    return ElementAbstract;
});