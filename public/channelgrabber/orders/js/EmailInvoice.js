define([
    'Orders/OrdersBulkActionAbstract',
    'Orders/SaveCheckboxes'
], function(
    OrdersBulkActionAbstract,
    saveCheckboxes
) {
    function EmailInvoice()
    {
        OrdersBulkActionAbstract.call(this);
    }

    EmailInvoice.prototype = Object.create(OrdersBulkActionAbstract.prototype);

    EmailInvoice.prototype.invoke = function()
    {
        if (this.getOrders().length) {
            this.validate();
        }
    };

    EmailInvoice.prototype.validate = function()
    {
        this.getNotificationHandler().notice("Preparing Orders for Email");
        this.sendAjaxRequest(
            this.getElement().data("url"),
            $.extend({"validate": true}, this.getDataToSubmit()),
            function(data) {
                if (data.emailed > 0) {

                } else {
                    this.process();
                }
            },
            function(request, textStatus, errorThrown) {
                return this.getNotificationHandler().ajaxError(request, textStatus, errorThrown);
            },
            this
        );
    };

    EmailInvoice.prototype.process = function(includePreviouslySent)
    {
        this.getNotificationHandler().notice("Marking Orders for Email");
        this.sendAjaxRequest(
            this.getElement().data("url"),
            $.extend({"includePreviouslySent": (includePreviouslySent !== undefined ? includePreviouslySent : false)}, this.getDataToSubmit()),
            function(data) {
                if (data.emailing) {
                    saveCheckboxes.setSavedCheckboxes(this.getOrders())
                        .setSavedCheckAll(this.isAllSelected());
                    return this.getNotificationHandler().success("Orders Marked for Email");
                } else if (!data.error) {
                    return this.getNotificationHandler().error("Failed to marked Orders for Email");
                }
                this.getNotificationHandler().error(data.error);
            },
            function(request, textStatus, errorThrown) {
                return this.getNotificationHandler().ajaxError(request, textStatus, errorThrown);
            },
            this,
            {
                complete: function() {
                    if (!this.getDataTableElement().length) {
                        return;
                    }
                    this.getDataTableElement().cgDataTable("redraw");
                    saveCheckboxes.refreshCheckboxes(datatable);
                }
            }
        );
    };

    return EmailInvoice;
});
