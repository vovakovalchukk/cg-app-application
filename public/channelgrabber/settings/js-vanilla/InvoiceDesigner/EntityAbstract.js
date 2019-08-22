define(function() {
    const EntityAbstract = function() {
        this.set = function(field, value, populating, topicPublishSettings) {
            const data = this.getData();
            data[field] = value;

            if (populating) {
                return;
            }

            if (!Array.isArray(topicPublishSettings)) {
                this.publish();
                return;
            }

            for (let settings of topicPublishSettings) {
                this.publishTopic(settings.topicName, settings)
            }

            this.publish();
        };
    };

    EntityAbstract.prototype.get = function(field) {
        const data = this.getData();
        return data[field];
    };

    return EntityAbstract;
});