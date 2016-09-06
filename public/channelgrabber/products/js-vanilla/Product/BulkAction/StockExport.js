define(['BulkAction/ProgressCheckAbstract'], function(ProgressCheckAbstract)
{
    function StockExport(
        startMessage,
        progressMessage,
        endMessage
    ) {
        ProgressCheckAbstract.call(this, startMessage, progressMessage, endMessage);
    };

    StockExport.prototype = Object.create(ProgressCheckAbstract.prototype);

    StockExport.prototype.getDataToSubmit = function()
    {
        return {};
    };

    StockExport.prototype.getProgressKeyName = function()
    {
        return "stockExportProgressKey";
    };

    StockExport.prototype.getCheckUrl = function()
    {
        return "/products/stock/csv/export/check";
    };

    StockExport.prototype.getProgressUrl = function()
    {
        return "/products/stock/csv/export/progress";
    };

    return StockExport;
});
