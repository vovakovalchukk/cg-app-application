define(function() {
    return function(notifications) {
        var notifications = notifications;

        this.action = function(event) {
            var datatable = $(this).data("datatable");
            if (!datatable) {
                return;
            }

            var ajax = {
                url: $(this).data("url"),
                type: "POST",
                dataType: 'json',
                success : function(data) {
                    if (data.archived) {
                        return notifications.success("Archived Successfully");
                    } else if (!data.error) {
                        return notifications.error("Failed to archived Orders");
                    }
                    notifications.error(data.error);
                },
                error: function (error, textStatus, errorThrown) {
                    return notifications.ajaxError(error, textStatus, errorThrown);
                },
                complete: function() {
                    if (datatable) {
                        $("#" + datatable).cgDataTable("redraw");
                    }
                }
            };

            if ($("#" + datatable + "-select-all").is(":checked")) {
                ajax.url += "/" + $("#" + datatable).data("filterId");
            } else {
                var orders = $("#" + datatable).cgDataTable("selected", ".order-id");
                if (!orders.length) {
                    return;
                }
                ajax.data = {
                    orders: orders
                };
            }

            notifications.notice("Archiving Orders");
            $.ajax(ajax);
        };
    };
});