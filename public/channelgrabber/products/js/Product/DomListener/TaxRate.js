define([
    'Product/Service'
], function (
    service
) {
    var TaxRate = function()
    {
        var vatRateSelector;

        this.getVatRateSelector = function()
        {
            return vatRateSelector;
        };

        this.setVatRateSelector = function(selector)
        {
            vatRateSelector = selector;
        };
    };

    TaxRate.prototype.init = function(vatRateSelector)
    {
        this.setVatRateSelector(vatRateSelector);
    };

    TaxRate.prototype.listen = function()
    {
        var self = this;
        $(document).on('change', this.getVatRateSelector(), function(event, container, value){
            self.save(container, value)
        });
    };

    TaxRate.prototype.save = function(target, value)
    {
        service.saveTaxRate(target, value);
    };

    return new TaxRate();
});