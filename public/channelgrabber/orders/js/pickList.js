define(['ProgressCheckAbstract'],
    function(ProgressCheckAbstract) {
    var PickListBulkAction = function(notifications, message)
    {
        ProgressCheckAbstract.call(this, notifications, message);
    };

    PickListBulkAction.prototype = Object.create(ProgressCheckAbstract.prototype);

    PickListBulkAction.MIN_ORDERS_FOR_NOTIFICATION = 7;
    PickListBulkAction.NOTIFICATION_FREQ_MS = 1000;

    PickListBulkAction.prototype.notifyTimeoutHandle = null;

    PickListBulkAction.prototype.getParam = function()
    {
        return "pickListProgressKey"
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
                form.find('input[name='+ this.getParam() +']').val(data.guid);
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
        var data = {};
        data[this.getParam()] = progressKey;
        return setInterval(function()
        {
            $.ajax({
                context: self,
                url: '/orders/picklist/progress',
                type: "POST",
                data: data,
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
