define([
    'Orders/OrdersBulkActionAbstract',
    'Orders/BulkActionService'
], function(
    OrdersBulkActionAbstract,
    BulkActionService
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

        this.getNotificationHandler().notice("Unlinking order...");
        return $.ajax({
            url: this.getElement().data("url"),
            data: {
                "orders": orders
            },
            context: this,
            type: "POST",
            dataType: 'json',
            success : function(data, textStatus, request) {
                if (data.error) {
                    var itid = request.getResponseHeader('ITID-Response');
                    return this.getNotificationHandler().error("Failed to unlink order. Please contact support and provide the following reference code:\n"+itid);
                }
                this.getNotificationHandler().success("Successfully unlinked order.");

                orders.map(function (orderId) {
                    BulkActionService.refresh(data.bulkActions[orderId]);
                });
                $('#linked-orders').remove();
            },
            error: function(request, textStatus, errorThrown) {
                return this.getNotificationHandler().ajaxError(request, textStatus, errorThrown);
            }
        });
    };

    return Pay;
});