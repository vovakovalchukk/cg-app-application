define([
    'BulkActionAbstract'
], function(
    BulkActionAbstract
) {
    var Delete = function()
    {
        BulkActionAbstract.call(this);
    };

    Delete.prototype = Object.create(BulkActionAbstract.prototype);

    Delete.URL = '/products/delete';
    Delete.MESSAGE_SUCCESS = 'Products deleted successfully';
    Delete.MESSAGE_PENDING = 'Deleting products';

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
            productIds.push(parseInt(self.getLastPartOfHyphenatedString(domId)));
        });

        this.getNotificationHandler().notice(Delete.MESSAGE_PENDING);
        var data = {productIds: productIds};
        this.sendAjaxRequest(
            Delete.URL,
            data,
            this.handleSuccess,
            null,
            this
        );
    };

    Delete.prototype.handleSuccess = function()
    {
        this.getNotificationHandler().success(Delete.MESSAGE_SUCCESS);
    };

    return new Delete();
});