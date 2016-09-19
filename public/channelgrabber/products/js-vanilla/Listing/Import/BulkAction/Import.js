define([
    'BulkActionAbstract',
    'Listing/Import/Service'
], function(
    BulkActionAbstract,
    service
) {
    var Import = function()
    {
        BulkActionAbstract.call(this);

        this.getService = function()
        {
            return service;
        };
    };

    Import.prototype = Object.create(BulkActionAbstract.prototype);

    Import.URL = '/products/listing/import/import';
    Import.MESSAGE_SUCCESS = 'Listings imported successfully';
    Import.MESSAGE_PENDING = 'Importing listings';

    Import.prototype.invoke = function()
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

        this.getNotificationHandler().notice(Import.MESSAGE_PENDING);
        var data = {listingIds: listingIds};
        this.sendAjaxRequest(
            Import.URL,
            data,
            this.handleSuccess,
            null,
            this
        );
    };

    Import.prototype.handleSuccess = function()
    {
        this.getNotificationHandler().success(Import.MESSAGE_SUCCESS);
        this.getService().refreshDatatable();
    };

    return new Import();
});