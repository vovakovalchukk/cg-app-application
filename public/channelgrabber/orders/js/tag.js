define(function() {
    return function(notifications) {
        var notifications = notifications;
        this.action = function(event) {
            var datatable = $(this).data("datatable");
            var orders = $(this).data("orders");

            if (!orders && datatable) {
                orders = $("#" + datatable).cgDataTable("selected", ".order-id");
            }

            if (!orders.length) {
                return;
            }

            var tag = $(this).data("tag") || $.trim(window.prompt("Name of Tag:", "tag"));
            if (!tag.length) {
                return;
            }

            notifications.notice("Updating Order Tags");
            $.ajax({
                url: $(this).data("url"),
                type: "POST",
                dataType: 'json',
                data: {
                    'orders': orders,
                    'tag': tag
                },
                success : function(data) {
                    if (data.tagged) {
                        return notifications.success("Tagged Successfully");
                    } else if (!data.error) {
                        return notifications.error("Failed to apply Tags");
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
        }
    };
});