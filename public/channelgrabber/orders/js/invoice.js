define(function() {
    var InvoiceBulkAction = function(notifications, message)
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

    InvoiceBulkAction.MIN_INVOICES_FOR_NOTIFICATION = 7;
    InvoiceBulkAction.NOTIFICATION_FREQ_MS = 5000;

    InvoiceBulkAction.prototype.notifyTimeoutHandle = null;

    InvoiceBulkAction.prototype.getDataTableElement = function()
    {
        var dataTable = this.getElement().data("datatable");
        if (!dataTable) {
            return $();
        }
        return $("#" + dataTable);
    };

    InvoiceBulkAction.prototype.isDataTableCheckedAll = function()
    {
        var dataTable = this.getElement().data("datatable");
        if (!dataTable) {
            return false;
        }
        return $("#" + dataTable + "-select-all").is(":checked");
    };

    InvoiceBulkAction.prototype.getOrders = function()
    {
        var orders = this.getElement().data("orders");
        if (orders) {
            return orders;
        }
        return this.getDataTableElement().cgDataTable("selected", ".checkbox-id");
    };

    InvoiceBulkAction.prototype.getFilterId = function()
    {
        return this.getDataTableElement().data("filterId");
    };

    InvoiceBulkAction.prototype.getUrl = function()
    {
        var url = this.getElement().data("url") || "";
        if (this.isDataTableCheckedAll()) {
            url += "/" + this.getFilterId();
        }
        return url;
    };

    InvoiceBulkAction.prototype.getFormElement = function(orders)
    {
        var form = $("<form><input name='progressKey' value='' /></form>").attr("action", this.getUrl()).attr("method", "POST").hide();
        for (var index in orders) {
            form.append(function() {
                return $("<input />").attr("name", "orders[]").val(orders[index]);
            });
        }
        return form;
    };

    InvoiceBulkAction.prototype.action = function(event)
    {
        var orders = [];
        var orderCount = 0;
        if (!this.isDataTableCheckedAll()) {
            orders = this.getOrders();
            if (!orders.length) {
                return;
            }
            orderCount = orders.length;
        } else {
            orderCount = $('#datatable').dataTable().fnSettings().fnRecordsDisplay();
        }

        var fadeOut = true;
        this.getNotifications().notice('Preparing to generate invoices', fadeOut);

        $.ajax({
            context: this,
            url: this.getUrl()+'/check',
            type: "POST",
            dataType: 'json',
            success : function(data) {
                if (!data.allowed) {
                    return this.getNotifications().error('You do not have permission to do that');
                }

                if (orderCount >= InvoiceBulkAction.MIN_INVOICES_FOR_NOTIFICATION) {
                    this.notifyTimeoutHandle = this.setupProgressCheck(orderCount, data.guid);
                } else {
                    this.getNotifications().notice(this.getMessage(), true);
                }

                var form = this.getFormElement(orders);
                form.find('input[name=progressKey]').val(data.guid);
                $("body").append(form);
                form.submit().remove();
            },
            error: function(error, textStatus, errorThrown) {
                return this.getNotifications().ajaxError(error, textStatus, errorThrown);
            }
        });
    };

    InvoiceBulkAction.prototype.setupProgressCheck = function(total, progressKey)
    {
        var self = this;

        return setInterval(function()
        {
            $.ajax({
                context: self,
                url: self.getUrl()+'/progress',
                type: "POST",
                data: {progressKey: progressKey},
                dataType: 'json',
                success : function(data) {
                    if (!data.hasOwnProperty('progressCount')) {
                        return this.getNotifications().error('Unable to determine the number of processed invoices');
                    }

                    var fadeOut = true;
                    this.getNotifications().notice('Generated ' + data.progressCount + ' of ' + total, fadeOut);

                    if (data.progressCount == total) {
                        clearTimeout(this.notifyTimeoutHandle);
                        this.getNotifications().success('Finished generating invoices', fadeOut);
                    }
                },
                error: function(error, textStatus, errorThrown) {
                    clearTimeout(this.notifyTimeoutHandle);
                    return this.getNotifications().ajaxError(error, textStatus, errorThrown);
                }
            });
        }, InvoiceBulkAction.NOTIFICATION_FREQ_MS);
    };

    return InvoiceBulkAction;
});