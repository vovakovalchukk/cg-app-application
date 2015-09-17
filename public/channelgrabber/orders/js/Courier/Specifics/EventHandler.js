define([], function()
{
    function EventHandler(service)
    {
        this.getService = function()
        {
            return service;
        };

        var init = function()
        {
            this.listenToNavLinkClicks()
                .listenToParcelsChange();
        };
        init.call(this);
    }

    EventHandler.SELECTOR_NAV_LINKS = 'a.courier-specifics-nav-link';
    EventHandler.SELECTOR_PARCEL_INPUT = '.courier-parcels .courier-parcels-input';

    EventHandler.prototype.listenToNavLinkClicks = function()
    {
        var service = this.getService();
        $(EventHandler.SELECTOR_NAV_LINKS).click(function(event)
        {
            event.preventDefault();
            var element = this;
            service.courierLinkChosen($(element).attr('href'));
        });
        return this;
    };

    EventHandler.prototype.listenToParcelsChange = function()
    {
        var service = this.getService();
        $(document).on('save', EventHandler.SELECTOR_PARCEL_INPUT, function(event, value, element)
        {
            service.parcelsChanged();
        });
        return this;
    };

    return EventHandler;
});