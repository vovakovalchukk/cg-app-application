define(['Orders/OrdersBulkActionAbstract'], function(OrdersBulkActionAbstract)
{
    var ProgressCheckAbstract = function(
        notifications, //deprecated
        startMessage,
        progressMessage,
        endMessage
    ) {
        OrdersBulkActionAbstract.call(this);

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

    ProgressCheckAbstract.prototype = Object.create(OrdersBulkActionAbstract.prototype);

    ProgressCheckAbstract.prototype.notifyTimeoutHandle = null;

    ProgressCheckAbstract.prototype.getFilterId = function()
    {
        return this.getDataTableElement().data("filterId");
    };

    ProgressCheckAbstract.prototype.getUrl = function()
    {
        return this.getElement().data("url") || "";
    };

    ProgressCheckAbstract.prototype.getFormElement = function(guid)
    {
        var form = $("<form><input name='" + this.getParam() + "' value='" + guid + "' /></form>")
            .attr("action", this.getUrl()).attr("method", "POST")
            .hide();
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

    ProgressCheckAbstract.prototype.invoke = function()
    {
        var orders = this.getOrders();
        if (!orders.length) {
            return;
        }
        var orderCount = orders.length;

        var fadeOut = true;
        this.getNotificationHandler().notice(this.getStartMessage(), fadeOut);

        $.ajax({
            context: this,
            url: this.getCheckUrl(),
            type: "POST",
            dataType: 'json',
            success : function(data) {
                if (!data.allowed) {
                    return this.getNotificationHandler().error('You do not have permission to do that');
                }
                this.getNotificationHandler().notice(this.getProgressMessage(), true);

                if (orderCount >= this.getMinOrders()) {
                    this.notifyTimeoutHandle = this.setupProgressCheck(orderCount, data.guid);
                }

                var form = this.getFormElement(data.guid);
                $("body").append(form);
                form.submit().remove();
            },
            error: function(error, textStatus, errorThrown) {
                return this.getNotificationHandler().ajaxError(error, textStatus, errorThrown);
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
                    this.getNotificationHandler().notice('Generated ' + data.progressCount + ' of ' + total, fadeOut);

                    if (data.progressCount == total) {
                        clearTimeout(this.notifyTimeoutHandle);
                        fadeOut = true;
                        this.getNotificationHandler().success(self.getEndMessage(), fadeOut);
                    }
                },
                error: function(error, textStatus, errorThrown) {
                    clearTimeout(this.notifyTimeoutHandle);
                    return this.getNotificationHandler().ajaxError(error, textStatus, errorThrown);
                }
            });
        }, self.getFreq());
    };

    return ProgressCheckAbstract;
});
