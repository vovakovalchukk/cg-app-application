define(function () {
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

    Ajax.prototype.saveTaxRate = function(productId, taxRateId, callback)
    {
        return $.ajax({
            'url' : '/products/taxRate',
            'data' : {productId: productId, taxRateId: taxRateId },
            'method' : 'POST',
            'dataType' : 'json',
            'success' : function() {
                callback(null);
            },
            'error' : callback
        });
    };

    return new Ajax();
});
