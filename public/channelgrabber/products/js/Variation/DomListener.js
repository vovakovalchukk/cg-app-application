define([
    'Product/DomListener/Search'
], function(
    SearchDomListener
) {
    var DomListener = function(service)
    {
        this.getService = function()
        {
            return service;
        };

        var init = function()
        {
            this.listenForVariationToggle()
                .listenForProductsFetched()
                .listenForProductsRendered();
        };
        init.call(this);
    };

    DomListener.SELECTOR_PRODUCT_CONTAINER = '.product-container';
    DomListener.CLASS_EXPAND_BUTTON = 'product-variation-expand-button';
    DomListener.CLASS_EXPAND_AJAX = 'expand-button-ajax';

    DomListener.prototype.listenForVariationToggle = function()
    {
        var service = this.getService();
        $(document).off('click', '.' + DomListener.CLASS_EXPAND_BUTTON).on('click', '.' + DomListener.CLASS_EXPAND_BUTTON, function() {
            service.toggleVariations($(this).closest(DomListener.SELECTOR_PRODUCT_CONTAINER));
        });
        return this;
    };

    DomListener.prototype.listenForProductsFetched = function()
    {
        var service = this.getService();
        $(document).on(SearchDomListener.EVENT_PRODUCTS_FETCHED, function(event, products)
        {
            service.productsFetched(products);
        });
        return this;
    };

    DomListener.prototype.listenForProductsRendered = function()
    {
        var service = this.getService();
        $(document).on(SearchDomListener.EVENT_PRODUCTS_RENDERED, function(event, products)
        {
            service.productsRendered(products);
        });
        return this;
    };

    return DomListener;
});
