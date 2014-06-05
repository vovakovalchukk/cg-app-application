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

            var tag = $(this).data("tag") || $.trim(window.prompt("Name of Tag:", "tag"));
            if (!tag.length) {
                return;
            }

            apply.call(
                this,
                getAppendUrl.call(this),
                tag,
                orders,
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

            var url;
            if ($(this).is(":checked")) {
                url = getAppendUrl.call(this);
            } else {
                url = getRemoveUrl.call(this);
            }

            apply.call(
                this,
                url,
                tag,
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

        var getAppendUrl = function() {
            return getUrl.call(this, 'append');
        };

        var getRemoveUrl = function() {
            return getUrl.call(this, 'remove');
        };

        var getUrl = function(action) {
            return Mustache.render($(this).data("url"), {action: action});
        };

        var apply = function(url, tag, orders, ajaxSettings) {
            var ajax = {
                context: this,
                url: url,
                type: "POST",
                dataType: 'json',
                data: {
                    'tag': tag,
                    'orders': orders
                },
                success : function(data) {
                    return notifications.success("Tagged Successfully");
                },
                error: function(error, textStatus, errorThrown) {
                    return notifications.ajaxError(error, textStatus, errorThrown);
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