define([
    'jquery',
    'Messages/Thread/Storage/Ajax',
    'Messages/Thread/Storage/Events'
], function(
    $,
    storage,
    StorageEvents
) {
    var Service = function()
    {
        this.getStorage = function()
        {
            return storage;
        };
    };

    Service.ASSIGNEE_ACTIVE_USER = 'active-user';
    Service.STATUS_RESOLVED = 'resolved';

    Service.prototype.fetchCollectionByFilter = function(filter, page, callback)
    {
        this.getStorage().fetchCollectionByFilter(filter, page, function(threads) {
            $(document).trigger(StorageEvents.THREADS_FETCHED, [threads]);
            callback(threads);
        });
    };

    Service.prototype.fetch = function(id, callback)
    {
        this.getStorage().fetch(id, function(thread) {
            $(document).trigger(StorageEvents.THREAD_FETCHED, [thread]);
            callback(thread);
        });
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
        var data = {id: thread.getId(), assignedUserId: thread.getAssignedUserId()};
        this.getStorage().saveData(data, callback);
    };

    Service.prototype.resolve = function(thread, callback)
    {
        thread.setStatus(Service.STATUS_RESOLVED);
        this.saveStatus(thread, callback);
    };

    Service.prototype.saveStatus = function(thread, callback)
    {
        var data = {id: thread.getId(), status: thread.getStatus()};
        this.getStorage().saveData(data, callback);
    };

    return new Service();
});
