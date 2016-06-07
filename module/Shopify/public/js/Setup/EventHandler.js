define([], function() {
    function EventHandler(service)
    {
        this.getService = function()
        {
            return service;
        };

        var init = function()
        {
            this
                .initSubmitListener()
                .initClickListener();
        };

        init.call(this);
    }

    EventHandler.SELECTOR_FORM = 'form.account-form-holder';
    EventHandler.SELECTOR_LINK = '#linkAccount';

    EventHandler.prototype.initSubmitListener = function()
    {
        var service = this.getService();
        $(EventHandler.SELECTOR_FORM).off('submit.shopify').on('submit.shopify', function(event) {
            event.preventDefault();
            service.linkAccount();
        });
        return this;
    };

    EventHandler.prototype.initClickListener = function()
    {
        $(EventHandler.SELECTOR_LINK).off('click.shopify').on('click.shopify', function(event) {
            $(EventHandler.SELECTOR_FORM).submit();
        });
        return this;
    };

    return EventHandler;
});
