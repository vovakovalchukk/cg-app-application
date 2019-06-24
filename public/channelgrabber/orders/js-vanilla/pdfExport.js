define([
    'Orders/OrdersBulkActionAbstract',
    'mustache'
],function(
    OrdersBulkActionAbstract,
    Mustache
) {
    var pdfExport = function(notifications, popupTemplate)
    {
        OrdersBulkActionAbstract.call(this);


        var init = function() {
            var self = this;
        };
        init.call(this);
    };

    pdfExport.prototype = Object.create(OrdersBulkActionAbstract.prototype);

    return pdfExport;
});
