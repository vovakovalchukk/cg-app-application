define([
    'Product/Service',
    'EventCollator'
], function (service, eventCollator)
{
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

        this.getEventCollator = function()
        {
            return eventCollator;
        }
    };

    TaxRate.EVENT_COLLATOR_TYPE = 'ProductTaxRate';

    TaxRate.prototype.init = function(vatRateSelector)
    {
        this.setVatRateSelector(vatRateSelector);
        var self = this;

        $(document).on('change', this.getVatRateSelector(), function(event, container){
            self.triggerRequestMadeEvent(container);
        });

        $(document).on(eventCollator.getQueueTimeoutEventPrefix() + TaxRate.EVENT_COLLATOR_TYPE, function(event, data) {
            self.save(data[0])
        });
    };

    TaxRate.prototype.triggerRequestMadeEvent = function(container)
    {
        var unique = true;
        $(document).trigger(this.getEventCollator().getRequestMadeEvent(), [
            TaxRate.EVENT_COLLATOR_TYPE, container, unique
        ]);
    };

    TaxRate.prototype.save = function(container)
    {
        service.saveTaxRate(container);
    };

    return new TaxRate();
});