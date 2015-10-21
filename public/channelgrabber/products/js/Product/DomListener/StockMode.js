define([], function ()
{
    var StockMode = function(service)
    {
        this.getService = function()
        {
            return service;
        };

        var init = function()
        {
            this.listenForStockModeChange();
        };
        init.call(this);
    };

    StockMode.SELECTOR = '.stock-mode-holder';

    StockMode.prototype.listenForStockModeChange = function()
    {
        var service = this.getService();;
        $(document).on('change', StockMode.SELECTOR, function(event, element, value) {
            var productId = $(element).attr('id').split('-').pop();
            service.saveStockModeForProduct(productId, value);
        });
    };

    return StockMode;
});
