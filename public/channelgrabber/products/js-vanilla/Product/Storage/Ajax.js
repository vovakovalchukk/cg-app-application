define(function() {
    var Ajax = function() {
    };
    
    Ajax.prototype.fetchByFilter = async function(filter, callback) {
        try {
            let data = await $.ajax({
                'url': '/products/ajax',
                'data': {'filter': filter.toObject()},
                'method': 'POST',
                'dataType': 'json',
                'success': function(data) {
                    if (!callback) {
                        return;
                    }
                    callback(data);
                },
                'error': function() {
                    throw 'Unable to load products';
                }
            });
            return data;
        } catch (err) {
            console.error(err);
        }
    };
    
    Ajax.prototype.saveTaxRate = function(productId, taxRateId, memberState, callback) {
        return $.ajax({
            'url': '/products/taxRate',
            'data': {productId: productId, taxRateId: taxRateId, memberState: memberState},
            'method': 'POST',
            'dataType': 'json',
            'success': function() {
                callback(null);
            },
            'error': callback
        });
    };
    
    Ajax.prototype.saveStockMode = function(productId, stockMode, callback) {
        return $.ajax({
            'url': '/products/stockMode',
            'data': {id: productId, stockMode: stockMode},
            'method': 'POST',
            'dataType': 'json',
            'success': function(response) {
                callback(response);
            },
            'error': function(response) {
                n.ajaxError(response);
            }
        });
    };
    
    Ajax.prototype.saveStockLevel = function(productId, stockLevel, callback) {
        return $.ajax({
            'url': '/products/stockLevel',
            'data': {id: productId, stockLevel: stockLevel},
            'method': 'POST',
            'dataType': 'json',
            'success': function(response) {
                callback(response);
            },
            'error': function(response) {
                n.ajaxError(response);
            }
        });
    };
    
    return new Ajax();
});
