define([
    'BulkActionAbstract',
    'Listing/Import/Service'
], function(
    BulkActionAbstract,
    service
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
        this.getService().refresh();
    };

    return new Hide();
});