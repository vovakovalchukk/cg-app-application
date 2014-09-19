define([
    'BulkActionAbstract'
], function(
    BulkActionAbstract
) {
    var Hide = function()
    {
        BulkActionAbstract.call(this);

        this.getService = function()
        {
            return service;
        };
    };

    Hide.prototype = Object.create(BulkActionAbstract.prototype);

    Hide.URL = '/products/listing/import/hide';
    Hide.MESSAGE_SUCCESS = 'Listings hidden successfully';

    Hide.prototype.invoke = function()
    {
        var self = this;
        var listingIds = [];
        var domIds = this.getSelectedIds();
        if (domIds.length == 0) {
            return;
        }
        domIds.forEach(function(domId)
        {
            listingIds.push(parseInt(self.getLastPartOfHyphenatedString(domId)));
        });

        var data = {listingIds: listingIds};
        this.sendAjaxRequest(
            Hide.URL,
            data,
            this.handleSuccess,
            null,
            this
        );
    };

    Hide.prototype.handleSuccess = function()
    {
        this.getNotificationHandler().success(Hide.MESSAGE_SUCCESS);
        // TODO: once we have filters call apply-filters here instead to force a reload over ajax
        window.location.reload();
    };

    return new Hide();
});