define(['Orders/BulkActionAbstract'], function(BulkActionAbstract)
{
    var ProgressCheckAbstract = function(
        notifications,
        startMessage,
        progressMessage,
        endMessage
    ) {
        BulkActionAbstract.call(this);

        this.getNotifications = function()
        {
            return notifications;
        };

        this.getStartMessage = function()
        {
            return startMessage;
        };

        this.getProgressMessage = function()
        {
            return progressMessage;
        };

        this.getEndMessage = function()
        {
            return endMessage;
        };
    };

    ProgressCheckAbstract.prototype = Object.create(BulkActionAbstract.prototype);

    ProgressCheckAbstract.prototype.notifyTimeoutHandle = null;

    ProgressCheckAbstract.prototype.getOrders = function()
    {
        var orders = this.getElement().data("orders");
        if (orders) {
            return orders;
        }
        return this.getDataTableElement().cgDataTable("selected", ".checkbox-id");
    };

    ProgressCheckAbstract.prototype.getFilterId = function()
    {
        return this.getDataTableElement().data("filterId");
    };

    ProgressCheckAbstract.prototype.getUrl = function()
    {
        return this.getElement().data("url") || "";
    };

    ProgressCheckAbstract.prototype.getFormElement = function(orders)
    {
        var form = $("<form><input name='" + this.getParam() + "' value='' /></form>").attr("action", this.getUrl()).attr("method", "POST").hide();
        var data = this.getDataToSubmit();
        for (var key in data) {
            var name = key;
            var value = data[key];
            if (!(value instanceof Array)) {
                form.append(this.getFormInputElement(name, value));
                continue;
            }
            name += '[]';
            for (var count in value) {
                form.append(this.getFormInputElement(name, value[count]));
            }
        }
        return form;
    };

    ProgressCheckAbstract.prototype.getFormInputElement = function(name, value)
    {
        return '<input name="'+name+'" value="'+value+'" />';
    };

    ProgressCheckAbstract.prototype.getParam = function()
    {
        throw 'ProgressCheckAbstract::getParam must be overridden by child class';
    };

    ProgressCheckAbstract.prototype.getCheckUrl = function()
    {
        throw 'ProgressCheckAbstract::getCheckUrl must be overridden by child class';
    };

    ProgressCheckAbstract.prototype.getProgressUrl = function()
    {
        throw 'ProgressCheckAbstract::getProgressUrl must be overridden by child class';
    };

    ProgressCheckAbstract.prototype.getMinOrders = function()
    {
        throw 'ProgressCheckAbstract::getMinOrders must be overridden by child class';
    };

    ProgressCheckAbstract.prototype.getFreq = function()
    {
        throw 'ProgressCheckAbstract::getFreq must be overridden by child class';
    };

    ProgressCheckAbstract.prototype.action = function(event)
    {
        var orders = this.getOrders();
        if (!orders.length) {
            return;
        }
        var orderCount = orders.length;

        var fadeOut = true;
        this.getNotifications().notice(this.getStartMessage(), fadeOut);

        $.ajax({
            context: this,
            url: this.getCheckUrl(),
            type: "POST",
            dataType: 'json',
            success : function(data) {
                if (!data.allowed) {
                    return this.getNotifications().error('You do not have permission to do that');
                }
                this.getNotifications().notice(this.getProgressMessage(), true);

                if (orderCount >= this.getMinOrders()) {
                    this.notifyTimeoutHandle = this.setupProgressCheck(orderCount, data.guid);
                }

                var form = this.getFormElement(orders);
                form.find('input[name=' + this.getParam() + ']').val(data.guid);
                $("body").append(form);
                form.submit().remove();
            },
            error: function(error, textStatus, errorThrown) {
                return this.getNotifications().ajaxError(error, textStatus, errorThrown);
            }
        });
    };

    ProgressCheckAbstract.prototype.setupProgressCheck = function(total, progressKey)
    {
        var self = this;
        var data = {};
        data[this.getParam()] = progressKey;
        return setInterval(function()
        {
            $.ajax({
                context: self,
                url: self.getProgressUrl(),
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
                        this.getNotifications().success(self.getEndMessage(), fadeOut);
                    }
                },
                error: function(error, textStatus, errorThrown) {
                    clearTimeout(this.notifyTimeoutHandle);
                    return this.getNotifications().ajaxError(error, textStatus, errorThrown);
                }
            });
        }, self.getFreq());
    };

    return ProgressCheckAbstract;
});
