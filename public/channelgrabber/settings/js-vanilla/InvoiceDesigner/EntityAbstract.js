define(function() {
    const EntityAbstract = function() {
        this.set = function(field, value, populating, topicsToPublishSettings) {
            const data = this.getData();
            data[field] = value;

            if (populating) {
                return;
            }

            if (!Array.isArray(topicsToPublishSettings)) {
                this.publish();
                return;
            }

            let performedUpdates = [];
            // this is used to account for entity changes that have taken place as a result of topic
            // publishes. We can then pass this information onwards to give a full picture of what
            // has changed as a result of this set call
            function entityUpdateReceiver(entityUpdateObject) {
                performedUpdates.push(entityUpdateObject)
            }

            for (let settings of topicsToPublishSettings) {
                settings.recordEntityUpdate = entityUpdateReceiver;
                this.publishTopic(settings.topicName, settings);
            }

            performedUpdates.push({
                entity: this.getEntityName(),
                field,
                value
            });

            this.publish(performedUpdates);
        };
    };

    EntityAbstract.prototype.get = function(field) {
        const data = this.getData();
        return data[field];
    };

    return EntityAbstract;
});