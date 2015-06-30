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

    Service.ASSIGNEE_ACTIVE_USER = 'active-user';

    Service.prototype.fetchCollectionByFilter = function(filter, callback)
    {
        this.getStorage().fetchCollectionByFilter(filter, callback);
    };

    Service.prototype.fetch = function(id, callback)
    {
        this.getStorage().fetch(id, callback);
    };

    Service.prototype.assignToActiveUser = function(thread, callback)
    {
        // Special value for the current user
        // callback will be passed the new version of the thread with the actual ID set
        thread.setAssignedUserId(Service.ASSIGNEE_ACTIVE_USER);
        this.saveAssigned(thread, callback);
    };

    Service.prototype.saveAssigned = function(thread, callback)
    {
        this.getStorage().saveAssigned(thread, callback);
    };

    return new Service();
});