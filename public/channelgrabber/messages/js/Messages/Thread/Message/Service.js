define([
    'Messages/Thread/Message/Storage/Ajax'
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

    Service.prototype.sendMessage = function(thread, messageBody, callback)
    {
        var data = {threadId: thread.getId(), body: messageBody};
        this.getStorage().saveData(data, callback);
    };

    return new Service();
});