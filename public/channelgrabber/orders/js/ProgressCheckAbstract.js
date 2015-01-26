define(function() {
    var ProgressCheckAbstract = function(notifications, message)
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

    ProgressCheckAbstract.prototype.getDataTableElement = function()
    {
        var dataTable = this.getElement().data("datatable");
        if (!dataTable) {
            return $();
        }
        return $("#" + dataTable);
    };

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
        for (var index in orders) {
            form.append(function() {
                return $("<input />").attr("name", "orders[]").val(orders[index]);
            });
        }
        return form;
    };

    ProgressCheckAbstract.prototype.getParam = function()
    {
        throw 'ProgressCheckAbstract::getParam must be overridden by child class';
    };

    return ProgressCheckAbstract;
});
