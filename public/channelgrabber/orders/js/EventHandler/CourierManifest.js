define([], function()
{
    function CourierManifest(service)
    {
        this.getService = function()
        {
            return service;
        };

        var init = function()
        {
            // No-op. Listener setup triggered by the service (../CourierManifest.js)
        };
        init.call(this);
    }

    CourierManifest.SELECTOR_ACCOUNT_SELECT = '#courier-manifest-account-select';
    CourierManifest.SELECTOR_GENERATE_BUTTON = '#courier-manifest-generate-button';
    CourierManifest.SELECTOR_HISTORIC_YEARS = '#courier-manifest-historic-years-select';
    CourierManifest.SELECTOR_HISTORIC_MONTHS = '#courier-manifest-historic-months-select';
    CourierManifest.SELECTOR_HISTORIC_DATES = '#courier-manifest-historic-dates-select';

    CourierManifest.prototype.listenForAccountSelect = function()
    {
        var service = this.getService();
        $(CourierManifest.SELECTOR_ACCOUNT_SELECT).off('change').on('change', function(event, element, value)
        {
            service.accountSelected(value);
        });
        return this;
    };

    CourierManifest.prototype.listenForGenerateButtonClick = function()
    {
        var service = this.getService();
        $(CourierManifest.SELECTOR_GENERATE_BUTTON).off('click').on('click', function()
        {
            service.generateManifest();
        });
        return this;
    };

    CourierManifest.prototype.listenForHistoricYearSelect = function()
    {
        var service = this.getService();
        $(CourierManifest.SELECTOR_HISTORIC_YEARS).off('change').on('change', function(event, element, value)
        {
            service.historicYearSelected(value);
        });
        return this;
    };

    CourierManifest.prototype.listenForHistoricMonthSelect = function()
    {
        var service = this.getService();
        $(CourierManifest.SELECTOR_HISTORIC_MONTHS).off('change').on('change', function(event, element, value)
        {
            var year = $(CourierManifest.SELECTOR_HISTORIC_YEARS + ' input').val();
            service.historicMonthSelected(value, year);
        });
        return this;
    };

    CourierManifest.prototype.listenForHistoricDateSelect = function()
    {
        var service = this.getService();
        $(CourierManifest.SELECTOR_HISTORIC_DATES).off('change').on('change', function(event, element, value)
        {
            service.historicManifestSelected(value);
        });
        return this;
    };

    return CourierManifest;
});