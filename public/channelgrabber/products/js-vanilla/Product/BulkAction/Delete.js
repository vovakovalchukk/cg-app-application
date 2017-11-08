define([
    'BulkActionAbstract',
    'BulkAction/ProgressCheckAbstract'
], function(
    BulkActionAbstract,
    ProgressCheckAbstract
) {
    var clickHandlerSetup = false;

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

        this.clickHandlerSetup();
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
            function(response) {
                if (response.status == 422) {
                    this.getNotificationHandler().error(
                        'The following skus can\'t be deleted as they are used as linked products '
                        + this.generateUlList(response.responseJSON.nonDeletableSkuList)
                        + '<a class="js-product-search-by-sku" data-sku=\'' + JSON.stringify(response.responseJSON.listOfAncestorSkusWithDeletionPreventingLinks) + '\'>'
                        + 'Click here to view products which are preventing deletion</a>'
                    );

                    return;
                }
                this.getAjaxRequester().handleFailure(response);

            },
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


    Delete.prototype.generateUlList = function(array) {
        var list = '<ul>';

        array.forEach(function(listItem) {
            list += '<li>' + listItem + '</li>';
        });

        list += '</ul>';
        return list;
    };

    Delete.prototype.clickHandlerSetup = function() {
        if (clickHandlerSetup) {
            return;
        }

        document.getElementById("main-notifications").addEventListener("click", function(e) {
            if(e.target && e.target.className == "js-product-search-by-sku") {
                window.triggerEvent('getProductsBySku', {sku: JSON.parse(e.target.dataset.sku)});
            }
        });

        clickHandlerSetup = true;
    };

    return Delete;
});

