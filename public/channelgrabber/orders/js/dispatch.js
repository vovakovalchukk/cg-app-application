define(function() {
    return function(notifications) {
        var notifications = notifications;

        this.action = function(event) {
            event.stopImmediatePropagation();

            var datatable = $(this).data("datatable");
            var orders = $(this).data("orders");

            if (!orders && datatable) {
                orders = $("#" + datatable).cgDataTable("selected", ".order-id");
            }

            if (!orders.length) {
                return;
            }

            apply.call(
                this,
                orders,
                {
                    complete: function() {
                        var datatable = $(this).data("datatable");
                        if (datatable) {
                            $("#" + datatable).cgDataTable("redraw");
                        }
                    }
                }
            );
        };

        var apply = function(orders, ajaxSettings) {
            var ajax = {
                context: this,
                url: $(this).data("url"),
                type: "POST",
                dataType: 'json',
                data: {
                    'orders': orders
                },
                success : function(data) {
                    if (data.tagged) {
                        return notifications.success("Orders Marked for Dispatch");
                    } else if (!data.error) {
                        return notifications.error("Failed to marked Orders for Dispatch");
                    }
                    notifications.error(data.error);
                },
                error: function(request) {
                    try {
                        if (request.getResponseHeader('Content-Type') != 'application/json') {
                            throw "An Unknown Error has Occurred";
                        }

                        var response = $.parseJSON(request.responseText);
                        if (!response.message) {
                            throw "An Unknown Error has Occurred";
                        }

                        notifications.error(response.message);
                    } catch (err) {
                        notifications.error(err);
                    }
                }
            };

            if (ajaxSettings !== undefined) {
                $.extend(ajax, ajaxSettings);
            }

            notifications.notice("Marking Orders for Dispatch");
            return $.ajax(ajax);
        };
    };
});