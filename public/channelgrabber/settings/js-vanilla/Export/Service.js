define(['BulkAction/ProgressCheckAbstract'], function(ProgressCheckAbstract)
{
    function Service(
        startMessage,
        progressMessage,
        endMessage
    ) {
        ProgressCheckAbstract.call(this, startMessage, progressMessage, endMessage);
    };

    Service.prototype = Object.create(ProgressCheckAbstract.prototype);

    Service.prototype.getDataToSubmit = function()
    {
        return {};
    };

    Service.prototype.getProgressKeyName = function()
    {
        return "orderExportProgressKey";
    };

    return Service;
});
