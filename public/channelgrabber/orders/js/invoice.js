define(['Orders/ProgressCheckAbstract'],
function(ProgressCheckAbstract)
{
    var InvoiceBulkAction = function(
        startMessage,
        progressMessage,
        endMessage
    ) {
        ProgressCheckAbstract.call(this, startMessage, progressMessage, endMessage);
    };

    InvoiceBulkAction.MIN_INVOICES_FOR_NOTIFICATION = 7;
    InvoiceBulkAction.NOTIFICATION_FREQ_MS = 5000;

    InvoiceBulkAction.prototype = Object.create(ProgressCheckAbstract.prototype);

    InvoiceBulkAction.prototype.getProgressKeyName = function()
    {
        return "invoiceProgressKey";
    };

    InvoiceBulkAction.prototype.getCheckUrl = function()
    {
        return "/orders/invoice/check";
    };

    InvoiceBulkAction.prototype.getProgressUrl = function()
    {
        return "/orders/invoice/progress";
    };

    InvoiceBulkAction.prototype.getMinRecordsForProgress = function()
    {
        return InvoiceBulkAction.MIN_INVOICES_FOR_NOTIFICATION;
    };

    InvoiceBulkAction.prototype.getFreqMsForProgress = function()
    {
        return InvoiceBulkAction.NOTIFICATION_FREQ_MS;
    };

    return InvoiceBulkAction;
});
