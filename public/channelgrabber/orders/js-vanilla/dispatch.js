define([
    'Orders/OrdersBulkActionAbstract',
    'Orders/SaveCheckboxes',
    'Orders/StatusService',
    'Orders/TimelineService',
    'Orders/BulkActionService',
    'popup/confirm'
], function(
    OrdersBulkActionAbstract,
    saveCheckboxes,
    StatusService,
    TimelineService,
    BulkActionService,
    Confirm
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

        var self = this;
        var confirmationMessage = "Dispatching on OrderHub will dispatch on the relevant sales channel. Are you sure you want to dispatch " + orders.length + " orders?";

        var confirm = new Confirm(confirmationMessage, function (response) {
            if (response !== "Yes") {
                return;
            }

            var ajaxConfig = self.buildAjaxConfig();

            self.getNotificationHandler().notice("Marking Orders for Dispatch");
            return $.ajax(ajaxConfig);
        });
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
                        TimelineService.refresh(data.timelines[orderId]);
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
