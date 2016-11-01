define([
    'Orders/OrdersBulkActionAbstract'
], function(
    OrdersBulkActionAbstract
) {
    function Pay() {
        OrdersBulkActionAbstract.call(this);
    }

    Pay.prototype = Object.create(OrdersBulkActionAbstract.prototype);

    Pay.prototype.invoke = function () {
        var orders = this.getOrders();
        if (!orders.length) {
            return;
        }

        this.getNotificationHandler().notice("Marking Order as paid.");
        return $.ajax({

        });
    };

    return Pay;
});