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
            url: this.getElement().data("url"),
            data: {
                "orders": orders
            },
            context: this,
            type: "POST",
            dataType: 'json',
            success : function(data) {
                if (data.error) {
                    return this.getNotificationHandler().error("Failed to mark order as paid");
                }
                this.getNotificationHandler().success("Successfully marked order as paid");
            },
            error: function(request, textStatus, errorThrown) {
                return this.getNotificationHandler().ajaxError(request, textStatus, errorThrown);
            }
        });
    };

    return Pay;
});