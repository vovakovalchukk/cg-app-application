define([
    'Orders/OrdersBulkActionAbstract',
    'Orders/SaveCheckboxes',
    'popup/confirm'
], function(
    OrdersBulkActionAbstract,
    saveCheckboxes,
    ConfirmPopup
) {
    function EmailInvoice()
    {
        OrdersBulkActionAbstract.call(this);
    }

    EmailInvoice.TITLE_YES = 'Send all';
    EmailInvoice.TITLE_NO = 'Send unsent';

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
                    this.confirm(data);
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

    EmailInvoice.prototype.confirm = function(stats)
    {
        this.getNotificationHandler().clearNotifications();
        var self = this;
        new ConfirmPopup(
            CGMustache.get().renderTemplate(
                "{{emailed}} of the {{total}} invoices you're trying to send have already been emailed."
                + " Do you want to re-send them or send only invoices that have not been previously sent?",
                stats
            ),
            function(includePreviouslySent) {
                if (includePreviouslySent !== undefined) {
                    self.process(includePreviouslySent);
                }
            },
            [
                {title: EmailInvoice.TITLE_YES, value: ConfirmPopup.VALUE_YES},
                {title: EmailInvoice.TITLE_NO, value: ConfirmPopup.VALUE_NO}
            ],
            false
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
