define([
    'jquery'
], function (
    $
) {
    var Ajax = function () {};

    Ajax.prototype.fetchByFilter = function(filter, callback)
    {
        $.ajax({
            'url' : '/products/ajax',
            'data' : {'filter': filter.toObject()},
            'method' : 'POST',
            'dataType' : 'json',
            'success' : function(data) {
                callback(data['products']);
            },
            'error' : function () {
                throw 'Unable to load products';
            }
        });
    };

    Ajax.prototype.saveTaxRate = function(productId, taxRateId)
    {
        return $.ajax({
            'url' : '/products/taxRate',
            'data' : {productId: productId, taxRateId: taxRateId },
            'method' : 'POST',
            'dataType' : 'json',
            'success' : function(data) {
                n.success('Product tax rate updated successfully');
            },
            'error' : function(error, textStatus, errorThrown) {
                n.ajaxError(error, textStatus, errorThrown);
            }
        });
    };

    return new Ajax();
});