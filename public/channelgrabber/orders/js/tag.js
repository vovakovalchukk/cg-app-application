define(function() {
    return function(notifications) {
        var notifications = notifications;

        this.action = function(event) {
            event.stopImmediatePropagation()

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

            apply.call(
                this,
                tag,
                orders,
                true,
                {
                    complete: function() {
                        if (datatable) {
                            $("#" + datatable).cgDataTable("redraw");
                        }
                    }
                }
            );
        };

        this.checkbox = function(event) {
            event.stopImmediatePropagation()

            var tag = $(this).data("tag");
            var orders = $(this).data("orders");

            if (!tag || !orders || !orders.length) {
                return;
            }

            apply.call(
                this,
                tag,
                orders,
                $(this).is(":checked"),
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

        var apply = function(tag, orders, append, ajaxSettings) {
            var ajax = {
                context: this,
                url: $(this).data("url"),
                type: "POST",
                dataType: 'json',
                data: {
                    'tag': tag,
                    'orders': orders,
                    'append': append
                },
                success : function(data) {
                    if (data.tagged) {
                        return notifications.success("Tagged Successfully");
                    } else if (!data.error) {
                        return notifications.error("Failed to apply Tag");
                    }
                    notifications.error(data.error);
                },
                error: function() {
                    notifications.error("Network Error");
                }
            };

            if (ajaxSettings !== undefined) {
                $.extend(ajax, ajaxSettings);
            }

            notifications.notice("Updating Order Tag");
            return $.ajax(ajax);
        }
    };
});