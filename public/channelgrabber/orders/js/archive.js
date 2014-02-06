define(function() {
    return function(notifications) {
        var notifications = notifications;

        this.action = function(event) {
            var datatable = $(this).data("datatable");
            if (!datatable) {
                return;
            }

            var orders = $("#" + datatable).cgDataTable("selected", ".order-id");
            if (!orders.length) {
                return;
            }

            notifications.notice("Archiving Orders");
            $.ajax({
                url: $(this).data("url"),
                type: "POST",
                dataType: 'json',
                data: {
                    'orders': orders
                },
                success : function(data) {
                    if (data.tagged) {
                        return notifications.success("Archived Successfully");
                    } else if (!data.error) {
                        return notifications.error("Failed to archived Orders");
                    }
                    notifications.error(data.error);
                },
                error: function() {
                    notifications.error("Network Error");
                },
                complete: function() {
                    if (datatable) {
                        $("#" + datatable).cgDataTable("redraw");
                    }
                }
            });
        };
    };
});