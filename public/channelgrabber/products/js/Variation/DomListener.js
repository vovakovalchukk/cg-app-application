define([
    'jquery',
    'Variation/Service'
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
    DomListener.CLASS_EXPAND_BUTTON = 'product-variation-expand-button';

    DomListener.prototype.init = function()
    {
        var self = this;
        $(document).off('click', '.' + DomListener.CLASS_EXPAND_BUTTON).on('click', '.' + DomListener.CLASS_EXPAND_BUTTON, function() {
            self.getService().toggleVariations($(this).closest(DomListener.SELECTOR_PRODUCT_CONTAINER));
        });
    };

    DomListener.prototype.getClassExpandButton = function()
    {
        return DomListener.CLASS_EXPAND_BUTTON;
    };

    return new DomListener();
});