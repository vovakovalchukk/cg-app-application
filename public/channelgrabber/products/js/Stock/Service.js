define([
    'jquery'
], function (
    $
) {
    var Service = function()
    {
    };

    Service.prototype.save = function(stockLocationId, totalQuantity, eTagSelector)
    {
        $.ajax({
            url: 'products/stock/update',
            type: 'POST',
            dataType : 'json',
            data: {
                'stockLocationId': stockLocationId,
                'totalQuantity': totalQuantity,
                'eTag': $(eTagSelector).val()
            },
            success: function(data) {
                $(eTagSelector).val(data.eTag);
            },
            error: function(error, textStatus, errorThrown) {
                n.ajaxError(error, textStatus, errorThrown);
            }
        });
    };

    return new Service();
});