define([
], function (
) {
    var Service = function()
    {
    };

    Service.MIN_HHTP_CODE_ERROR = 400;

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
                    if (data.code && parseInt(data.code) < Service.MIN_HHTP_CODE_ERROR) {
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

    return new Service();
});