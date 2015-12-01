define([
    'KeyPress',
    'ElementWatcher'
], function (
    KeyPress,
    elementWatcher
) {
    var Search = function(service)
    {
        this.getService = function()
        {
            return service;
        };

        var init = function()
        {
            var self = this;
            elementWatcher.onInitialise(function() {
                self.listen();
            });
        };
        init.call(this);
    };

    Search.SELECTOR_INPUT = '.product-search-text';
    Search.SELECTOR_BUTTON = '.product-search-button';
    Search.EVENT_PRODUCTS_FETCHED = 'products-fetched';
    Search.EVENT_PRODUCTS_RENDERED = 'products-rendered';

    Search.prototype.listen = function()
    {
        var self = this;
        $(Search.SELECTOR_INPUT).off('keypress').on('keypress', function(event){
            if (event.which == KeyPress.ENTER) {
                self.search();
            }
        });
        $(Search.SELECTOR_BUTTON).off('click').on('click', function(event){
            self.search();
        });
    };

    Search.prototype.search = function()
    {
        this.getService().refresh();
    };

    Search.prototype.triggerProductsFetchedEvent = function(products)
    {
        $(document).trigger(Search.EVENT_PRODUCTS_FETCHED, [products]);
    };

    Search.prototype.triggerProductsRenderedEvent = function(products)
    {
        $(document).trigger(Search.EVENT_PRODUCTS_RENDERED, [products]);
    };

    Search.prototype.getProductsFetchedEvent = function()
    {
        return Search.EVENT_PRODUCTS_FETCHED;
    };

    Search.prototype.getProductsRenderedEvent = function()
    {
        return Search.EVENT_PRODUCTS_RENDERED;
    };

    return Search;
});