define([], function ()
{
    var EventHandler = function(service)
    {
        this.getService = function()
        {
            return service;
        };

        var init = function()
        {
            this.initSubmitListener();
        };
        init.call(this);
    };

    EventHandler.SELECTOR_FORM = '#carrier-account-form';
    EventHandler.SELECTOR_LINK_ACCOUNT = '#linkAccount';

    EventHandler.prototype.initSubmitListener = function()
    {
        var service = this.getService();
        $(EventHandler.SELECTOR_LINK_ACCOUNT).off('click').on('click', function()
        {
            service.save();
        });
        $(EventHandler.SELECTOR_FORM).off('submit').on('submit', function(event)
        {
            event.preventDefault();
        });
        return this;
    };

    return EventHandler;
});