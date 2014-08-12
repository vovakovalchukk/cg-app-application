define([
], function (
) {
    var Service = function()
    {
    };

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
                eTagCallback(data.eTag);
            },
            error: function(error, textStatus, errorThrown) {
                n.ajaxError(error, textStatus, errorThrown);
            }
        });
    };

    return new Service();
});