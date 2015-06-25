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

    Ajax.URL = '/messages/ajax';

    Ajax.prototype = Object.create(StorageAbstract.prototype);

    Ajax.prototype.fetchCollectionByFilter = function(filter, callback)
    {
        var self = this;
        this.getRequester().sendRequest(Ajax.URL, filter, function(response)
        {
            var threads = new Collection();
            for (var index in response.threads) {
                var thread = self.getMapper().fromJson(response.threads[index]);
                threads.attach(thread);
            }
            callback(threads);
        });
    };

    return new Ajax();
});