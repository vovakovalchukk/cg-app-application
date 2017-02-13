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
            success : function(data, textStatus, request) {
                console.log(data.timelines);
                if (data.error) {
                    var itid = request.getResponseHeader('ITID-Response');
                    return this.getNotificationHandler().error("Failed to mark order as paid. Please contact support and provide the following reference code:\n"+itid);
                }
                this.getNotificationHandler().success("Successfully marked order as paid");
                location.reload();
            },
            error: function(request, textStatus, errorThrown) {
                return this.getNotificationHandler().ajaxError(request, textStatus, errorThrown);
            }
        });
    };

    return Pay;
});