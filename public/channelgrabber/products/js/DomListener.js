define([
    'jquery',
    'Product/Service'
], function(
    $,
    service
) {
    var DomListener = function()
    {
        this.getService = function()
        {
            return service;
        };
    };

    DomListener.SELECTOR_PRODUCT_CONTAINER = '.product-container';
    DomListener.SELECTOR_EXPAND_BUTTON = '.product-variation-expand-button';

    DomListener.prototype.init = function()
    {
        var self = this;
        $(DomListener.SELECTOR_EXPAND_BUTTON).off('click').on('click', function() {
            self.getService().toggleVariations($(this).closest(DomListener.SELECTOR_PRODUCT_CONTAINER));
        });
    };

    return new DomListener();
});