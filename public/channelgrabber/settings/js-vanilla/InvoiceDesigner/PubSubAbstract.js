define(function() {
    var PubSubAbstract = function()
    {
        var subscribers = [];

        this.getSubscribers = function()
        {
            return subscribers;
        };
    };

    PubSubAbstract.PUBLISH_METHOD = 'publisherUpdate';

    PubSubAbstract.prototype.subscribe = function(subscriber)
    {
        if (!subscriber.hasMethods([PubSubAbstract.PUBLISH_METHOD, 'getId'])) {
            throw 'InvalidArgumentException: InvoiceDesigner\PubSubAbstract::subscribe() '+
                'must be passed a valid subscriber object';
        }

        this.getSubscribers().push(subscriber);
        return this;
    };

    PubSubAbstract.prototype.unsubscribe = function(subscriber)
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

    PubSubAbstract.prototype.publish = function()
    {
        var subscribers = this.getSubscribers();
        for (var key in subscribers) {
            subscribers[key][PubSubAbstract.PUBLISH_METHOD](this);
        }
        return this;
    };

    return PubSubAbstract;
});