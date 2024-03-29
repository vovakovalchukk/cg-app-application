define(['Orders/ProgressCheckAbstract'], function(ProgressCheckAbstract)
{
    var PickListBulkAction = function(
        startMessage,
        progressMessage,
        endMessage
    ) {
        ProgressCheckAbstract.call(this, startMessage, progressMessage, endMessage);
    };

    PickListBulkAction.MIN_ORDERS_FOR_NOTIFICATION = 99999999; //disable it temporarily
    PickListBulkAction.NOTIFICATION_FREQ_MS = 3000;

    PickListBulkAction.prototype = Object.create(ProgressCheckAbstract.prototype);

    PickListBulkAction.prototype.getProgressKeyName = function()
    {
        return "pickListProgressKey";
    };

    PickListBulkAction.prototype.getCheckUrl = function()
    {
        return "/orders/picklist/check";
    };

    PickListBulkAction.prototype.getProgressUrl = function()
    {
        return "/orders/picklist/progress";
    };

    PickListBulkAction.prototype.getMinRecordsForProgress = function()
    {
        return PickListBulkAction.MIN_ORDERS_FOR_NOTIFICATION;
    };

    PickListBulkAction.prototype.getFreqMsForProgress = function()
    {
        return PickListBulkAction.NOTIFICATION_FREQ_MS;
    };

    return PickListBulkAction;
});
