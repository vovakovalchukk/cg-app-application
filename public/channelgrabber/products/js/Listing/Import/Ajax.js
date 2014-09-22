define([
    'jquery'
], function (
    $
) {
    var Ajax = function ()
    {
    };

    Ajax.prototype.refresh = function(callback)
    {
        $.ajax({
            'url': '/products/listing/import/refresh',
            'type': 'POST',
            'success': function() {
                callback();
            }
        });
    };

    return new Ajax();
});