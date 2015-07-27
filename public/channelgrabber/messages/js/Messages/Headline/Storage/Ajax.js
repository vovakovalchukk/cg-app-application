define([
    'Messages/Headline/StorageAbstract',
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

    Ajax.URL_ENTITY = '/messages/ajax/headline';

    Ajax.prototype = Object.create(StorageAbstract.prototype);

    Ajax.prototype.fetch = function(organisationUnitId, callback)
    {
        var self = this;
        this.getRequester().sendRequest(Ajax.URL_ENTITY, {organisationUnitId: organisationUnitId}, function(response)
        {
            if (response.message) {
                n.error(response.message);
                return;
            }
            var headline = self.getMapper().fromJson(response.headline);
            callback(headline);
        });
    };

    return new Ajax();
});