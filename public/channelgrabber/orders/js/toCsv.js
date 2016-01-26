define(['Orders/ProgressCheckAbstract'], function(ProgressCheckAbstract)
{
    var ToCsvBulkAction = function(
        startMessage,
        progressMessage,
        endMessage
    ) {
        ProgressCheckAbstract.call(this, startMessage, progressMessage, endMessage);
    };

    ToCsvBulkAction.MIN_ORDERS_FOR_NOTIFICATION = 99999999; //disable it temporarily
    ToCsvBulkAction.NOTIFICATION_FREQ_MS = 3000;

    ToCsvBulkAction.prototype = Object.create(ProgressCheckAbstract.prototype);

    ToCsvBulkAction.prototype.getProgressKeyName = function()
    {
        return "toCsvProgressKey";
    };

    ToCsvBulkAction.prototype.getCheckUrl = function()
    {
        return "/orders/toCsv/check";
    };

    ToCsvBulkAction.prototype.getProgressUrl = function()
    {
        return "/orders/toCsv/progress";
    };

    ToCsvBulkAction.prototype.getMinRecordsForProgress = function()
    {
        return ToCsvBulkAction.MIN_ORDERS_FOR_NOTIFICATION;
    };

    ToCsvBulkAction.prototype.getFreqMsForProgress = function()
    {
        return ToCsvBulkAction.NOTIFICATION_FREQ_MS;
    };

    return ToCsvBulkAction;
});
