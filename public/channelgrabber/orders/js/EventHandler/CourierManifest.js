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
            this.listenForAccountSelect()
                .listenForGenerateButtonClick();
        };
        init.call(this);
    }

    CourierManifest.SELECTOR_ACCOUNT_SELECT = '#courier-manifest-account-select';
    CourierManifest.SELECTOR_GENERATE_BUTTON = '#courier-manifest-generate-button';

    CourierManifest.prototype.listenForAccountSelect = function()
    {
        var service = this.getService();
        $(document).on('change', CourierManifest.SELECTOR_ACCOUNT_SELECT, function(event, element, value)
        {
            service.accountSelected(value);
        });
        return this;
    };

    CourierManifest.prototype.listenForGenerateButtonClick = function()
    {
        var service = this.getService();
        $(document).on('click', CourierManifest.SELECTOR_GENERATE_BUTTON+'-shadow', function()
        {
            service.generateManifest();
        });
        return this;
    };

    return CourierManifest;
});