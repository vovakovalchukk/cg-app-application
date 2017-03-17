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
        var requestData = {productIds: productIds};
        this.sendAjaxRequest(
            Delete.URL,
            requestData,
            this.handleSuccess.bind(this, requestData),
            null,
            this
        );
    };

    Delete.prototype.handleSuccess = function(productIds)
    {
        this.getNotificationHandler().success(Delete.MESSAGE_SUCCESS);
        window.triggerEvent('productDeleted', productIds);
    };

    return new Delete();
});