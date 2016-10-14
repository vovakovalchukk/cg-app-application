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
            this.listenForDefaultStockModeChange()
                .listenForSaveButtonClick();
        };
        init.call(this);
    }

    EventHandler.SELECTOR_DEFAULT_STOCK_MODE_SELECT = '#settings-stock-default-stock-mode';
    EventHandler.SELECTOR_DEFAULT_STOCK_MODE_INPUT = '#settings-stock-default-stock-mode input[type="hidden"]';
    EventHandler.SELECTOR_DEFAULT_STOCK_LEVEL = '#settings-stock-default-stock-level';
    EventHandler.SELECTOR_SAVE_BUTTON = '#settings-stock-save-button';
    EventHandler.SELECTOR_FORM = '#settings-stock-form';

    EventHandler.prototype.listenForDefaultStockModeChange = function()
    {
        var service = this.getService();
        $(EventHandler.SELECTOR_DEFAULT_STOCK_MODE_SELECT).on('change', function(event, element, value)
        {
            service.defaultStockModeChanged(value);
        });
        return this;
    };

    EventHandler.prototype.listenForSaveButtonClick = function()
    {
        var service = this.getService();
        $(EventHandler.SELECTOR_SAVE_BUTTON).on('click', function(event)
        {
            event.preventDefault();
            service.save();
        });
        return this;
    };

    return EventHandler;
});