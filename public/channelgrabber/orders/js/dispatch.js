define([
    'Orders/OrdersBulkActionAbstract',
    'Orders/SaveCheckboxes'
], function(
    OrdersBulkActionAbstract,
    saveCheckboxes
) {
    function Dispatch()
    {
        OrdersBulkActionAbstract.call(this);

        this.getSaveCheckboxes = function()
        {
            return saveCheckboxes;
        };
    };

    Dispatch.prototype = Object.create(OrdersBulkActionAbstract.prototype);

    Dispatch.prototype.invoke = function()
    {
        var orders = this.getOrders();
        if (!orders.length) {
            return;
        }

        var ajaxConfig = this.buildAjaxConfig();

        this.getNotificationHandler().notice("Marking Orders for Dispatch");
        return $.ajax(ajaxConfig);
    };

    Dispatch.prototype.buildAjaxConfig = function()
    {
        var self = this;
        var datatable = this.getDataTableElement();
        var data = this.getDataToSubmit();
        return {
            url: this.getElement().data("url"),
            data: data,
            context: this,
            type: "POST",
            dataType: 'json',
            success : function(data)
            {
                if (data.dispatching) {
                    self.getSaveCheckboxes().setSavedCheckboxes(self.getOrders());
                    return self.getNotificationHandler().success("Orders Marked for Dispatch");
                } else if (!data.error) {
                    return self.getNotificationHandler().error("Failed to marked Orders for Dispatch");
                }
                self.getNotificationHandler().error(data.error);
            },
            error: function(request, textStatus, errorThrown)
            {
                return self.getNotificationHandler().ajaxError(request, textStatus, errorThrown);
            },
            complete: function()
            {
                if (!datatable.length) {
                    return;
                }
                datatable.cgDataTable("redraw");
                self.getSaveCheckboxes().refreshCheckboxes(datatable);
            }
        };
    };

    return Dispatch;
});
