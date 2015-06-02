define(['ProgressCheckAbstract'],
    function(ProgressCheckAbstract) {
        var ToCsvBulkAction = function(
            notifications,
            startMessage,
            progressMessage,
            endMessage
        ) {
            ProgressCheckAbstract.call(this, notifications, startMessage, progressMessage, endMessage);
        };

        ToCsvBulkAction.MIN_ORDERS_FOR_NOTIFICATION = 99999999; //disable it temporarily
        ToCsvBulkAction.NOTIFICATION_FREQ_MS = 3000;

        ToCsvBulkAction.prototype = Object.create(ProgressCheckAbstract.prototype);

        ToCsvBulkAction.prototype.getParam = function()
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

        ToCsvBulkAction.prototype.getMinOrders = function()
        {
            return ToCsvBulkAction.MIN_ORDERS_FOR_NOTIFICATION;
        };

        ToCsvBulkAction.prototype.getFreq = function()
        {
            return ToCsvBulkAction.NOTIFICATION_FREQ_MS;
        };

        return ToCsvBulkAction;
    }
);