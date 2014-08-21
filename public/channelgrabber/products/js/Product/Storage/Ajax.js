define([
    'jquery'
], function (
    $
) {
    var Ajax = function ()
    {
    };

    Ajax.prototype.fetchByFilter = function(filter, callback)
    {
        $.ajax({
            'url' : '/products/ajax',
            'data' : {'filter': filter.toArray()},
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

    return new Ajax();
});