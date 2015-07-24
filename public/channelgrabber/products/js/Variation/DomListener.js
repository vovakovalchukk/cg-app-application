define([
    'Product/DomListener/Search'
], function(
    productDomListener
) {
    var DomListener = function()
    {
    };

    DomListener.SELECTOR_PRODUCT_CONTAINER = '.product-container';
    DomListener.CLASS_EXPAND_BUTTON = 'product-variation-expand-button';
    DomListener.CLASS_EXPAND_AJAX = 'expand-button-ajax';

    DomListener.prototype.init = function(service)
    {
        $(document).off('click', '.' + DomListener.CLASS_EXPAND_BUTTON).on('click', '.' + DomListener.CLASS_EXPAND_BUTTON, function() {
            service.toggleVariations($(this).closest(DomListener.SELECTOR_PRODUCT_CONTAINER));
        });
        $(document).on(productDomListener.getProductsFetchedEvent(), function(event, products)
        {
            service.productsFetched(products);
        });
        $(document).on(productDomListener.getProductsRenderedEvent(), function(event, products)
        {
            service.productsRendered(products);
        });
    };

    DomListener.prototype.getClassExpandButton = function()
    {
        return DomListener.CLASS_EXPAND_BUTTON + " " + DomListener.CLASS_EXPAND_AJAX;
    };

    return new DomListener();
});
