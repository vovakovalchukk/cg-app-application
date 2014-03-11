define(function() {
    var InvoiceBulkAction = function(notifications, message) {
        var element;

        this.getElement = function () {
            return $(element);
        }

        this.setElement = function(newElement) {
            element = newElement;
        }

        this.getNotifications = function() {
            return notifications;
        };

        this.getMessage = function() {
            return message;
        };
    };

    InvoiceBulkAction.prototype.getDataTableElement = function() {
        var dataTable = this.getElement().data("datatable");
        if (!dataTable) {
            return $();
        }
        return $("#" + dataTable);
    };

    InvoiceBulkAction.prototype.getOrders = function() {
        var orders = this.getElement().data("orders");
        if (orders) {
            return orders;
        }
        return this.getDataTableElement().cgDataTable("selected", ".order-id");
    };

    InvoiceBulkAction.prototype.getUrl = function() {
        return this.getElement().data("url") || "";
    };

    InvoiceBulkAction.prototype.getFormElement = function(orders) {
        var form = $("<form></form>").attr("action", this.getUrl()).attr("method", "POST");
        for (var index in orders) {
            form.append(function() {
                return $("<input />").attr("name", "orders[]").val(orders[index]);
            });
        }
        return form;
    };

    InvoiceBulkAction.prototype.action = function(event) {
        var orders = this.getOrders();
        if (!orders.length) {
            return;
        }
        this.getNotifications().success(this.getMessage());
        this.getFormElement(orders).submit().remove();
    };

    return InvoiceBulkAction;
});