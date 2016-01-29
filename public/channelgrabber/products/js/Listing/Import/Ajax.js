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

    Ajax.prototype.refresh = function(callback)
    {
        this.getAjaxRequester().sendRequest('/products/listing/import/refresh', {}, function()
        {
            callback();
        });
    };

    return new Ajax();
});