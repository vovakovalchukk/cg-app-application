define(['Stock/DomListener'], function (StockDomListener)
{
    var Product = function(service)
    {
        this.getService = function()
        {
            return service;
        };

        var init = function()
        {
            this.listenForVatRateChange()
                .listenForStockModeUpdate();
        };
        init.call(this);
    };

    Product.SELECTOR_TAX_RATE = '.tax-rate-custom-select-holder';

    Product.prototype.listenForVatRateChange = function()
    {
        var self = this;
        $(document).on("change", Product.SELECTOR_TAX_RATE, function(event, container) {
            self.getService().saveTaxRate(container);
        });
        return this;
    };

    Product.prototype.listenForStockModeUpdate = function()
    {
        var self = this;
        $(document).on(StockDomListener.EVENT_STOCK_MODE_UPDATED, function(event, productId, stockMode, stockModeDefault, stockModeDesc, stockLevel)
        {
            self.getService().stockModeUpdated(productId, stockMode, stockModeDefault, stockModeDesc, stockLevel);
        });
    };

    return Product;
});
