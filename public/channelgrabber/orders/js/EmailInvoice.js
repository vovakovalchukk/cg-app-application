define([
    'Orders/SaveCheckboxes'
], function(
    saveCheckboxes
    ) {
    return function(notifications) {
        this.action = function(event) {
            event.stopImmediatePropagation();

            var datatable = $(this).data("datatable");
            var orders = $("#" + datatable).cgDataTable("selected", ".checkbox-id");

            if (!orders.length) {
                return;
            }

            var ajax = {
                url: $(this).data("url"),
                data: {
                    'orders': orders
                },
                complete: function() {
                    var dataTableElement = $('#' + datatable);
                    dataTableElement.cgDataTable("redraw");
                    saveCheckboxes.refreshCheckboxes(dataTableElement);
                }
            };

            apply.call(this, orders, ajax);
        };

        var apply = function(orders, ajaxSettings) {
            var ajax = {
                context: this,
                type: "POST",
                dataType: 'json',
                success : function(data) {
                    if (data.emailing) {
                        saveCheckboxes.setSavedCheckboxes(orders);
                        return notifications.success("Orders Marked for Email");
                    } else if (!data.error) {
                        return notifications.error("Failed to marked Orders for Email");
                    }
                    notifications.error(data.error);
                },
                error: function(request, textStatus, errorThrown) {
                    return notifications.ajaxError(request, textStatus, errorThrown);
                }
            };

            if (ajaxSettings !== undefined) {
                $.extend(ajax, ajaxSettings);
            }

            notifications.notice("Marking Orders for Email");
            return $.ajax(ajax);
        };
    };
});
