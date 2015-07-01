define([
    'Messages/Thread/Message/StorageAbstract',
    'AjaxRequester'
], function(
    StorageAbstract,
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

    Ajax.URL_ADD = '/messages/ajax/addMessage';

    Ajax.prototype = Object.create(StorageAbstract.prototype);

    Ajax.prototype.saveData = function(data, callback)
    {
        var self = this;
        this.getRequester().sendRequest(Ajax.URL_ADD, data, function(response)
        {
            if (response.message) {
                n.error(response.message);
                return;
            }
            var message = self.getMapper().fromJson(response.messageEntity);
            callback(message);
        });
    };

    return new Ajax();
});