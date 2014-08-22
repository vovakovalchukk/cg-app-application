define([
    'BulkActionAbstract',
    'Product/Service'
], function(
    BulkActionAbstract,
    service
) {
    var Delete = function()
    {
        BulkActionAbstract.call(this);

        this.getService = function()
        {
            return service;
        };
    };

    Delete.prototype = Object.create(BulkActionAbstract.prototype);

    Delete.URL = '/product/delete';
    Delete.MESSAGE_SUCCESS = 'Products deleted successfully';

    Delete.prototype.invoke = function()
    {
        var self = this;
        var productIds = [];
        var domIds = this.getSelectedIds();
        if (domIds.length == 0) {
            return;
        }
        domIds.forEach(function(domId)
        {
            productIds.push(self.getLastPartOfHyphenatedString(domId)); 
        });

        var data = {productIds: productIds};
        this.sendAjaxRequest(
            Delete.URL,
            data,
            this.handleSuccess
        );
    };

    Delete.prototype.handleSuccess = function()
    {
        this.getNotificationHandler().success(Delete.MESSAGE_SUCCESS);
        this.getService().refresh();
    };

    return new Delete();
});