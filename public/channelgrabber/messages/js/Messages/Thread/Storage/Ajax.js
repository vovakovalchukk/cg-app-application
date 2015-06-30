define([
    'Messages/Thread/StorageAbstract',
    'Messages/Thread/Collection',
    'AjaxRequester'
], function(
    StorageAbstract,
    Collection,
    requester
) {
    var Ajax = function()
    {
        StorageAbstract.call(this);

        this.getRequester = function()
        {
            return requester;
        };
    };

    Ajax.URL_COLLECTION = '/messages/ajax';
    Ajax.URL_ENTITY = '/messages/ajax/thread';
    Ajax.URL_SAVE = '/messages/ajax/save';

    Ajax.prototype = Object.create(StorageAbstract.prototype);

    Ajax.prototype.fetchCollectionByFilter = function(filter, callback)
    {
        var self = this;
        this.getRequester().sendRequest(Ajax.URL_COLLECTION, {filter: filter}, function(response)
        {
            if (response.message) {
                n.error(response.message);
                return;
            }
            var threads = new Collection();
            for (var index in response.threads) {
                var thread = self.getMapper().fromJson(response.threads[index]);
                threads.attach(thread);
            }
            callback(threads);
        });
    };

    Ajax.prototype.fetch = function(id, callback)
    {
        var self = this;
        this.getRequester().sendRequest(Ajax.URL_ENTITY, {id: id}, function(response)
        {
            if (response.message) {
                n.error(response.message);
                return;
            }
            var thread = self.getMapper().fromJson(response.thread);
            callback(thread);
        });
    };

    Ajax.prototype.saveAssigned = function(thread, callback)
    {
        var self = this;
        var data = {id: thread.getId(), assignedUserId: thread.getAssignedUserId()};
        this.getRequester().sendRequest(Ajax.URL_SAVE, data, function(response)
        {
            if (response.message) {
                n.error(response.message);
                return;
            }
            var thread = self.getMapper().fromJson(response.thread);
            callback(thread);
        });
    };

    return new Ajax();
});