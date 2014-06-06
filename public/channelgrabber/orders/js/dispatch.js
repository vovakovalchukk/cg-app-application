define(function() {
    return function(notifications) {
        this.action = function(event) {
            event.stopImmediatePropagation();

            var datatable = $(this).data("datatable");
            var orders = $(this).data("orders");
            var ajax = {
                url: $(this).data("url"),
                complete: function() {
                    if (datatable) {
                        $("#" + datatable).cgDataTable("redraw");
                    }
                }
            };

            if (datatable && $("#" + datatable + "-select-all").is(":checked")) {
                ajax.url += "/" + $("#" + datatable).data("filterId");
            } else {
                if (!orders && datatable) {
                    orders = $("#" + datatable).cgDataTable("selected", ".order-id");
                }

                if (!orders.length) {
                    return;
                }

                ajax.data = {
                    'orders': orders
                };
            }

            apply.call(this, orders, ajax);
        };

        var apply = function(orders, ajaxSettings) {
            var ajax = {
                context: this,
                type: "POST",
                dataType: 'json',
                success : function(data) {
                    if (data.dispatching) {
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