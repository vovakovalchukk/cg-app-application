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
    Ajax.URL_COUNTS = '/messages/:threadId/ajax/counts';
    Ajax.URL_SAVE = '/messages/ajax/save';

    Ajax.prototype = Object.create(StorageAbstract.prototype);

    Ajax.prototype.fetchCollectionByFilter = function(filter, page, callback, failureCallback)
    {
        var self = this;
        this.getRequester().sendRequest(Ajax.URL_COLLECTION, {filter: filter, page: page}, function(response)
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
        }, failureCallback);
    };

    Ajax.prototype.fetch = function(id, callback, failureCallback)
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
        }, failureCallback);
    };

    Ajax.prototype.fetchCounts = function(id, callback, failureCallback)
    {
        var self = this;
        this.getRequester().sendRequest(Ajax.URL_COUNTS.replace(':threadId', id), {}, function(response)
        {
            if (response.message) {
                n.error(response.message);
                return;
            }
            callback(response.counts);
        }, failureCallback);
    };

    Ajax.prototype.saveData = function(data, callback)
    {
        var self = this;
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
