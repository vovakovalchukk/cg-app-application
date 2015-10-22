define([], function ()
{
    var TaxRate = function(service)
    {
        this.getService = function()
        {
            return service;
        };

        var init = function()
        {
            this.listenForVatRateChange();
        };
        init.call(this);
    };

    TaxRate.SELECTOR = '.tax-rate-custom-select-holder';

    TaxRate.prototype.listenForVatRateChange = function()
    {
        var self = this;
        $(document).on("change", TaxRate.SELECTOR, function(event, container) {
            self.getService().saveTaxRate(container);
        });
        return this;
    };

    return TaxRate;
});
