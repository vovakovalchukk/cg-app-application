define([
    'KeyPress',
    'ElementWatcher'
], function (
    KeyPress,
    elementWatcher
) {
    var Search = function()
    {
        var service;
        this.setService = function(newService)
        {
            service = newService;
            return this;
        };
        this.getService = function()
        {
            return service;
        };
    };

    Search.SELECTOR_INPUT = '.product-search-text';
    Search.SELECTOR_BUTTON = '.product-search-button';
    Search.EVENT_PRODUCTS_RENDERED = 'products-rendered';

    Search.prototype.init = function(service)
    {
        this.setService(service);
        var self = this;
        elementWatcher.onInitialise(function() {
            self.listen();
        });
    };

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

    Search.prototype.triggerProductsRenderedEvent = function(products)
    {
        $(document).trigger(Search.EVENT_PRODUCTS_RENDERED, [products]);
    };

    Search.prototype.getProductsRenderedEvent = function()
    {
        return Search.EVENT_PRODUCTS_RENDERED;
    };

    return new Search();
});