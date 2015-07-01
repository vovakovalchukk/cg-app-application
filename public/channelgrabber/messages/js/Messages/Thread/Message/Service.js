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

    Service.prototype.sendMessage = function(thread, messageBody, resolve, callback)
    {
        var data = {threadId: thread.getId(), body: messageBody};
        if (resolve) {
            data.resolve = 1;
        }
        this.getStorage().saveData(data, callback);
    };

    return new Service();
});