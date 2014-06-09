define(function() {
    var InvoiceBulkAction = function(notifications, message) {
        var element;

        this.getElement = function () {
            return $(element);
        };

        this.setElement = function(newElement) {
            element = newElement;
        };

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

    InvoiceBulkAction.prototype.isDataTableCheckedAll = function() {
        var dataTable = this.getElement().data("datatable");
        if (!dataTable) {
            return false;
        }
        return $("#" + dataTable + "-select-all").is(":checked");
    };

    InvoiceBulkAction.prototype.getOrders = function() {
        var orders = this.getElement().data("orders");
        if (orders) {
            return orders;
        }
        return this.getDataTableElement().cgDataTable("selected", ".order-id");
    };

    InvoiceBulkAction.prototype.getFilterId = function() {
        return this.getDataTableElement().data("filterId");
    };

    InvoiceBulkAction.prototype.getUrl = function() {
        var url = this.getElement().data("url") || "";
        if (this.isDataTableCheckedAll()) {
            url += "/" + this.getFilterId();
        }
        return url;
    };

    InvoiceBulkAction.prototype.getFormElement = function(orders) {
        var form = $("<form></form>").attr("action", this.getUrl()).attr("method", "POST").hide();
        for (var index in orders) {
            form.append(function() {
                return $("<input />").attr("name", "orders[]").val(orders[index]);
            });
        }
        return form;
    };

    InvoiceBulkAction.prototype.action = function(event) {
        var orders = [];
        if (!this.isDataTableCheckedAll()) {
            orders = this.getOrders();
            if (!orders.length) {
                return;
            }
        }

        this.getNotifications().success(this.getMessage());

        var form = this.getFormElement(orders);
        $("body").append(form);
        form.submit().remove();
    };

    return InvoiceBulkAction;
});