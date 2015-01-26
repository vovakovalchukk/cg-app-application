define(function() {
    var PickListBulkAction = function(notifications, message)
    {
        var element;

        this.getElement = function ()
        {
            return $(element);
        };

        this.setElement = function(newElement)
        {
            element = newElement;
        };

        this.getNotifications = function()
        {
            return notifications;
        };

        this.getMessage = function()
        {
            return message;
        };
    };

    PickListBulkAction.MIN_ORDERS_FOR_NOTIFICATION = 7;
    PickListBulkAction.NOTIFICATION_FREQ_MS = 1000;

    PickListBulkAction.prototype.notifyTimeoutHandle = null;

    PickListBulkAction.prototype.getDataTableElement = function()
    {
        var dataTable = this.getElement().data("datatable");
        if (!dataTable) {
            return $();
        }
        return $("#" + dataTable);
    };

    PickListBulkAction.prototype.getOrders = function()
    {
        var orders = this.getElement().data("orders");
        if (orders) {
            return orders;
        }
        return this.getDataTableElement().cgDataTable("selected", ".checkbox-id");
    };

    PickListBulkAction.prototype.getFilterId = function()
    {
        return this.getDataTableElement().data("filterId");
    };

    PickListBulkAction.prototype.getUrl = function()
    {
        return this.getElement().data("url") || "";
    };

    PickListBulkAction.prototype.getFormElement = function(orders)
    {
        var form = $("<form><input name='pickListProgressKey' value='' /></form>").attr("action", this.getUrl()).attr("method", "POST").hide();
        for (var index in orders) {
            form.append(function() {
                return $("<input />").attr("name", "orders[]").val(orders[index]);
            });
        }
        return form;
    };

    PickListBulkAction.prototype.action = function(event)
    {
        var orders = this.getOrders();
        if (!orders.length) {
            return;
        }
        var orderCount = orders.length;

        var fadeOut = true;
        this.getNotifications().notice('Preparing to generate the pick list', fadeOut);

        $.ajax({
            context: this,
            url: '/orders/picklist/check',
            type: "POST",
            dataType: 'json',
            success : function(data) {
                if (!data.allowed) {
                    return this.getNotifications().error('You do not have permission to do that');
                }
                this.getNotifications().notice(this.getMessage(), true);

                if (orderCount >= PickListBulkAction.MIN_ORDERS_FOR_NOTIFICATION) {
                    this.notifyTimeoutHandle = this.setupProgressCheck(orderCount, data.guid);
                }

                var form = this.getFormElement(orders);
                form.find('input[name=pickListProgressKey]').val(data.guid);
                $("body").append(form);
                form.submit().remove();
            },
            error: function(error, textStatus, errorThrown) {
                return this.getNotifications().ajaxError(error, textStatus, errorThrown);
            }
        });
    };

    PickListBulkAction.prototype.setupProgressCheck = function(total, progressKey)
    {
        var self = this;

        return setInterval(function()
        {
            $.ajax({
                context: self,
                url: '/orders/picklist/progress',
                type: "POST",
                data: {"pickListProgressKey": progressKey},
                dataType: 'json',
                success : function(data) {
                    if (!data.hasOwnProperty('progressCount')) {
                        return;
                    }

                    var fadeOut = false;
                    this.getNotifications().notice('Generated ' + data.progressCount + ' of ' + total, fadeOut);

                    if (data.progressCount == total) {
                        clearTimeout(this.notifyTimeoutHandle);
                        fadeOut = true;
                        this.getNotifications().success('Finished generating the pick list', fadeOut);
                    }
                },
                error: function(error, textStatus, errorThrown) {
                    clearTimeout(this.notifyTimeoutHandle);
                    return this.getNotifications().ajaxError(error, textStatus, errorThrown);
                }
            });
        }, PickListBulkAction.NOTIFICATION_FREQ_MS);
    };

    return PickListBulkAction;
});
