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
                callback(data);
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

    Ajax.prototype.saveStockMode = function(productId, stockMode, eTag, callback)
    {
        return $.ajax({
            'url' : '/products/stockMode',
            'data' : { id: productId, stockMode: stockMode, eTag: eTag },
            'method' : 'POST',
            'dataType' : 'json',
            'success' : function(response) {
                callback(response);
            },
            'error' : function(response)
            {
                n.ajaxError(response);
            }
        });
    };

    Ajax.prototype.saveStockLevel = function(productId, stockLevel, eTag, callback)
    {
        return $.ajax({
            'url' : '/products/stockLevel',
            'data' : { id: productId, stockLevel: stockLevel, eTag: eTag },
            'method' : 'POST',
            'dataType' : 'json',
            'success' : function(response) {
                callback(response);
            },
            'error' : function(response)
            {
                n.ajaxError(response);
            }
        });
    };

    return new Ajax();
});
