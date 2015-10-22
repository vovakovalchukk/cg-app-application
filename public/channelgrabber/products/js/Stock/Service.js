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

    Service.prototype.save = function(stockLocationId, totalQuantity, eTag, eTagCallback)
    {
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

    Service.prototype.saveStockLevel = function(productId, value, etagElement)
    {
        if (parseInt(value) == NaN || parseInt(value) < 0) {
            n.error('Stock level must be a number greater than or equal to zero.');
            return;
        }
        var self = this;
        var eTag = etagElement.val();
        this.getDeferredQueue().queue(function() {
            return self.getStorage().saveStockLevel(productId, value, eTag, function(response) {
                etagElement.val(response.eTag);
                n.success('Product stock level updated successfully');
            });
        });
    };

    return Service;
});