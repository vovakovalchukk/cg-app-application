define([
    'Orders/OrdersBulkActionAbstract',
    'BulkAction/ProgressCheckAbstract'
], function(
    OrdersBulkActionAbstract,
    BulkActionProgressCheckAbstract
) {
    var ProgressCheckAbstract = function(
        notifications, //deprecated
        startMessage,
        progressMessage,
        endMessage
    ) {
        BulkActionProgressCheckAbstract.call(this);
        OrdersBulkActionAbstract.call(this);

        this.getStartMessage = function()
        {
            return startMessage;
        };

        this.getProgressMessage = function()
        {
            return progressMessage;
        };

        this.getEndMessage = function()
        {
            return endMessage;
        };
    };

    ProgressCheckAbstract.prototype = Object.create(BulkActionProgressCheckAbstract.prototype);
    for (var method in OrdersBulkActionAbstract.prototype) {
        ProgressCheckAbstract.prototype[method] = OrdersBulkActionAbstract.prototype[method];
    }

    ProgressCheckAbstract.prototype.invoke = function()
    {
        var orders = this.getOrders();
        if (!orders.length) {
            return;
        }

        // Can't save the filter as part of the main call as we're getting PDF / CSV data back so can't get the ID back
        this.saveFilterOnly();

        // Call parent
        BulkActionProgressCheckAbstract.prototype.invoke.call(this);
    };

    ProgressCheckAbstract.prototype.getRecordCountForProgress = function()
    {
        var orderCount = this.getOrders().length;
        if (this.isAllSelected()) {
            orderCount = this.getTotalRecordCount();
        }
        return orderCount;
    };

    return ProgressCheckAbstract;
});
