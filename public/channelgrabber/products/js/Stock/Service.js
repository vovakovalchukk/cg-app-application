define([
    'Stock/DomListener',
    'Product/Storage/Ajax',
    'DeferredQueue'
], function (
    DomListener,
    storage,
    DeferredQueue
) {
    var Service = function()
    {
        var domListener;
        var deferredQueue;

        this.getStorage = function()
        {
            return storage;
        };

        this.getDomListener = function()
        {
            return domListener;
        };

        this.getDeferredQueue = function()
        {
            return deferredQueue;
        };

        var init = function()
        {
            domListener = new DomListener(this);
            deferredQueue = new DeferredQueue();
        };
        init.call(this);
    };

    Service.MIN_HTTP_CODE_ERROR = 400;
    Service.SELECTOR_STOCK_TABLE = '.stock-table';
    Service.SELECTOR_STOCK_ROW_PREFIX = '#stock-row-';

    Service.prototype.save = function(stockLocationId, totalQuantity, eTag, eTagCallback)
    {
        n.notice('Saving stock total');
        $.ajax({
            url: 'products/stock/update',
            type: 'POST',
            dataType : 'json',
            data: {
                'stockLocationId': stockLocationId,
                'totalQuantity': totalQuantity,
                'eTag': eTag
            },
            success: function(data) {
                if (data.eTag) {
                    eTagCallback(data.eTag);
                    n.success('Stock was updated successfully');
                    return;
                }
                if (data.message) {
                    if (data.code && parseInt(data.code) < Service.MIN_HTTP_CODE_ERROR) {
                        n.success(data.message);
                        return;
                    }

                    n.error(data.message);
                    return;
                }
                n.error('An unknown error occurred');
            },
            error: function(error, textStatus, errorThrown) {
                n.ajaxError(error, textStatus, errorThrown);
            }
        });
    };

    Service.prototype.saveStockLevel = function(productId, stockLevel)
    {
        if (parseInt(stockLevel) == NaN || parseInt(stockLevel) < 0) {
            n.error('Stock level must be a number greater than or equal to zero.');
            return;
        }
        n.notice('Saving stock level');
        var self = this;
        var eTag = $('#etag_'+productId).val();
        this.getDeferredQueue().queue(function() {
            return self.getStorage().saveStockLevel(productId, stockLevel, eTag, function(response) {
                for (var id in response.eTags) {
                    $('#etag_'+id).val(response.eTags[id]);
                    $('#product-stock-level-'+id).val(stockLevel);
                }
                n.success('Product stock level updated successfully');
            });
        });
    };

    Service.prototype.saveStockModeForProduct = function(productId, value, eTagElement)
    {
        n.notice('Saving stock mode');
        var self = this;
        var eTag = eTagElement.val();
        this.getDeferredQueue().queue(function() {
            return self.getStorage().saveStockMode(productId, value, eTag, function(response) {
                eTagElement.val(response.eTags.productId);
                for (var variationId in response.eTags) {
                    var eTag = response.eTags[variationId];
                    $(Service.SELECTOR_STOCK_ROW_PREFIX + variationId + ' ' + DomListener.SELECTOR_STOCK_PROD_ETAG).val(eTag);
                }
                self.getDomListener().triggerStockModeUpdatedEvent(productId, value, response.stockModeDesc, response.stockLevel);
                n.success('Product stock mode updated successfully');
            });
        });
    };

    return Service;
});