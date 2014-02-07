define(function() {
    return function(notifications) {
        var notifications = notifications;
        var that = this;

        this.action = function(event) {
            var datatable = $(this).data("datatable");
            if (!datatable) {
                return;
            }

            var orders = $("#" + datatable).cgDataTable("selected", ".order-id");
            if (!orders.length) {
                return;
            }

            notifications.notice('Adding orders to batch');
            $.ajax({
                url: $(this).data("url"),
                type: "POST",
                dataType: 'json',
                data: {
                    'orders': orders
                },
                success : function(data) {
                    notifications.success($(that).data("Orders successfully added"));
                    if (datatable) {
                        $("#" + datatable).cgDataTable("redraw");
                    }
                },
                error: function (error, textStatus, errorThrown) {
                    return notifications.ajaxError(error, textStatus, errorThrown);
                }
            });
        };
    };
});
