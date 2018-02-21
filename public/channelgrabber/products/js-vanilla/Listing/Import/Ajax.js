define([
    'AjaxRequester'
], function (
    ajaxRequester
) {
    var Ajax = function ()
    {
        this.getAjaxRequester = function()
        {
            return ajaxRequester;
        };
    };

    Ajax.prototype.refresh = function(accounts)
    {
        this.getAjaxRequester().sendRequest('/products/listing/import/refresh', {accounts: accounts || []});
    };

    Ajax.prototype.refreshDetails = function(callback)
    {
        this.getAjaxRequester().sendRequest('/products/listing/import/refreshDetails', {}, function(details) {
            callback(details);
        });
    };

    return new Ajax();
});