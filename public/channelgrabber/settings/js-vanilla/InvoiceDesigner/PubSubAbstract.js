define([
    'InvoiceDesigner/PubSub/Topics'
], function(
    Topics
) {
    var PubSubAbstract = function() {
        let subscribers = [];

        this.getSubscribers = function() {
            return subscribers;
        };

        this.getTopics = function() {
            return Topics.getTopics();
        };

        this.getTopicNames = function() {
            return Topics.getTopicNames();
        }
    };

    PubSubAbstract.PUBLISH_METHOD = 'publisherUpdate';

    PubSubAbstract.prototype.subscribe = function(subscriber) {
        if (!subscriber.hasMethods([PubSubAbstract.PUBLISH_METHOD, 'getId'])) {
            throw 'InvalidArgumentException: InvoiceDesigner\PubSub\Abstract::subscribe() ' +
            'must be passed a valid subscriber object';
        }

        this.getSubscribers().push(subscriber);
        return this;
    };

    PubSubAbstract.prototype.subscribeToTopic = function(topic, callback) {
        const topics = this.getTopics();
        if (!topics[topic]) {
            topics[topic] = [];
        }
        topics[topic].push(callback);

        return {
            topic,
            callback
        };
    };

    PubSubAbstract.prototype.unsubscribe = function(subscriber) {
        var subscribers = this.getSubscribers();
        for (var key in subscribers) {
            if (subscribers[key].getId() == subscriber.getId()) {
                this.getSubscribers().splice(key, 1);
                break;
            }
        }
        return this;
    };

    PubSubAbstract.prototype.publish = function(performedUpdates) {
        var subscribers = this.getSubscribers();
        for (var key in subscribers) {
            subscribers[key][PubSubAbstract.PUBLISH_METHOD](this, performedUpdates);
        }
        return this;
    };

    PubSubAbstract.prototype.publishTopic = function(topic, settings) {
        const topics = this.getTopics();
        if (!topics[topic]) {
            return;
        }

        let topicCallbacks = topics[topic];
        settings = settings || [];

        for (let callback of topicCallbacks) {
            callback(settings);
        }
    };

    return PubSubAbstract;
});