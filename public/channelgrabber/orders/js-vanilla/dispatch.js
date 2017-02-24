define([
    'Orders/OrdersBulkActionAbstract',
    'Orders/SaveCheckboxes',
    'Orders/StatusService',
    'Orders/BulkActionService'
], function(
    OrdersBulkActionAbstract,
    saveCheckboxes,
    StatusService,
    BulkActionService
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
                    var orders = self.getOrders();
                    this.setFilterId(data.filterId);
                    self.getSaveCheckboxes().setSavedCheckboxes(orders)
                        .setSavedCheckAll(this.isAllSelected());
                    orders.map(function (orderId) {
                        StatusService.refresh(data.statuses[orderId]);
                        BulkActionService.refresh(data.bulkActions[orderId]);
                    });
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
