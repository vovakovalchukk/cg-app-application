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
                .listenForStockModeChange();
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

    Product.prototype.listenForStockModeChange = function()
    {
        var self = this;
        $(document).on(StockDomListener.EVENT_STOCK_MODE_CHANGED, function(event, productId, stockMode, stockModeDesc, stockLevel)
        {
            self.getService().stockModeChanged(productId, stockMode, stockModeDesc, stockLevel);
        });
    };

    return Product;
});
