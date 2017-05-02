define([
    'BulkActionAbstract',
    'BulkAction/ProgressCheckAbstract'
], function(
    BulkActionAbstract,
    ProgressCheckAbstract
) {
    var Delete = function(
        startMessage,
        progressMessage,
        endMessage,
        countMessage
    ) {
        var progressKey;
        var productIds = [];

        this.setProgressKey = function(newProgressKey)
        {
            progressKey = newProgressKey;
            return this;
        };

        this.getProgressKey = function()
        {
            return progressKey;
        };

        this.setProductIds = function(newProductIds)
        {
            productIds = newProductIds;
            return this;
        };

        this.getProductIds = function()
        {
            return productIds;
        };

        BulkActionAbstract.call(this);
        ProgressCheckAbstract.call(this, startMessage, progressMessage, endMessage, countMessage);
    };

    // Multiple inheritance. Note: the ordering of these is important - BulkAction before ProgressCheck
    Delete.prototype = Object.create(BulkActionAbstract.prototype);
    for (var method in ProgressCheckAbstract.prototype) {
        if (!ProgressCheckAbstract.prototype.hasOwnProperty(method)) {
            continue;
        }
        Delete.prototype[method] = ProgressCheckAbstract.prototype[method];
    }

    Delete.URL = '/products/delete';

    Delete.prototype.getUrl = function()
    {
        return Delete.URL;
    };

    Delete.prototype.getProgressKeyName = function()
    {
        return 'progressKey';
    };

    Delete.prototype.getCheckData = function()
    {
        return this.getDataToSubmit();
    };

    Delete.prototype.getDataToSubmit = function()
    {
        var self = this;
        var productIds = [];
        var domIds = this.getSelectedIds();
        if (domIds.length == 0) {
            return;
        }
        domIds.forEach(function(domId)
        {
            productIds.push(parseInt(self.getLastPartOfHyphenatedString(domId)));
        });
        return {"productIds": productIds};
    };

    Delete.prototype.submitData = function(guid)
    {
        var requestData = this.getDataToSubmit();
        requestData[this.getProgressKeyName()] = guid;
        this.sendAjaxRequest(
            Delete.URL,
            requestData,
            function(response) {
                if (requestData.productIds.length >= this.getMinRecordsForProgress()) {
                    // do nothing, the progress check will handle it
                    return;
                }
                this.getNotificationHandler().success(this.getEndMessage());
                this.progressFinished();
            },
            null,
            this
        );
    };

    Delete.prototype.getMinRecordsForProgress = function()
    {
        return 6;
    };

    Delete.prototype.progressFinished = function()
    {
        var data = this.getDataToSubmit();
        window.triggerEvent('productDeleted', data);
    };

    return Delete;
});