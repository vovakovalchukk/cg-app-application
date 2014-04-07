define(['popup/mustache'], function(Popup) {
    return function(notifications, reasons) {
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
                    if (data.cancelling) {
                        return notifications.success("Order marked for Cancellation");
                    } else if (!data.error) {
                        return notifications.error("Failed to mark Order for Cancellation");
                    }
                    return notifications.error(data.error);
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

            var popup = new Popup("/channelgrabber/orders/template/popups/cancelOptions.html", {
                title: "Cancellation Reason",
                reasons: function(){
                    var mappedReasons = [];
                    $.each(reasons, function(key, value) {
                        mappedReasons.push({name: value});
                    });
                    return mappedReasons;
                }
            });

            if (ajaxSettings !== undefined) {
                $.extend(ajax, ajaxSettings);
            }

            popup.show();
            notifications.notice("Marking Orders for Cancellation");
            return $.ajax(ajax);
        };
    };
});