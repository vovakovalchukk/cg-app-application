define([
    'Messages/Thread/Storage/Ajax'
], function(
    storage
) {
    var Service = function()
    {
        this.getStorage = function()
        {
            return storage;
        };
    };

    Service.prototype.fetchCollectionByFilter = function(filter, callback)
    {
        return this.getStorage().fetchCollectionByFilter(filter, callback);
    };

    Service.prototype.fetch = function(id, callback)
    {
        return this.getStorage().fetch(id, callback);
    };

    return new Service();
});