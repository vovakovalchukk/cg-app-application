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

    Service.prototype.wrapCollapsibleSections = function (messageBody) {

        var regex = /((?:^\>.*?$[\r\n]*)+)/gm;
        var replace = '<span class="collapsible-section">$&</span>';

        return messageBody.replace(regex, replace);
    };

    return new Service();
});