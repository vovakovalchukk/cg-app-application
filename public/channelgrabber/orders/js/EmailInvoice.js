define([
    'Orders/OrdersBulkActionAbstract',
    'Orders/SaveCheckboxes'
], function(
    OrdersBulkActionAbstract,
    saveCheckboxes
) {
    function EmailInvoice()
    {
        OrdersBulkActionAbstract.call(this);
    }

    EmailInvoice.prototype = Object.create(OrdersBulkActionAbstract.prototype);

    EmailInvoice.prototype.invoke = function()
    {
        var orders = this.getOrders();
        if (!orders.length) {
            return;
        }

        var ajaxConfig = this.buildAjaxConfig();
        this.getNotificationHandler().notice("Marking Orders for Email");
        return $.ajax(ajaxConfig);
    };

    EmailInvoice.prototype.buildAjaxConfig = function()
    {
        var datatable = this.getDataTableElement();
        var data = this.getDataToSubmit();
        return {
            context: this,
            type: "POST",
            dataType: 'json',
            data: data,
            url: this.getElement().data("url"),
            complete: function() {
                if (!datatable.length) {
                    return;
                }
                datatable.cgDataTable("redraw");
                saveCheckboxes.refreshCheckboxes(datatable);
            },
            success : function(data) {
                if (data.emailing) {
                    saveCheckboxes.setSavedCheckboxes(this.getOrders());
                    return this.getNotificationHandler().success("Orders Marked for Email");
                } else if (!data.error) {
                    return this.getNotificationHandler().error("Failed to marked Orders for Email");
                }
                this.getNotificationHandler().error(data.error);
            },
            error: function(request, textStatus, errorThrown) {
                return this.getNotificationHandler().ajaxError(request, textStatus, errorThrown);
            }
        };
    };

    return EmailInvoice;
});
