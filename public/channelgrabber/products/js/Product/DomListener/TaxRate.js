define([
    'Product/Service'
], function (service)
{
    var TaxRate = function()
    {
        this.getService = function()
        {
            return service;
        }
    };

    TaxRate.prototype.init = function(vatRateSelector)
    {
        var self = this;
        $(document).on("change", vatRateSelector, function(event, container) {
            self.getService().saveTaxRate(container);
        });
    };

    return new TaxRate();
});
