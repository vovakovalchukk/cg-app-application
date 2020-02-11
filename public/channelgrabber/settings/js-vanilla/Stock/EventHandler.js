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
                .listenForLowThresholdToggleChange()
                .listenForSaveButtonClick();
        };
        init.call(this);
    }

    EventHandler.SELECTOR_DEFAULT_STOCK_MODE_SELECT = '#settings-stock-default-stock-mode';
    EventHandler.SELECTOR_DEFAULT_STOCK_MODE_INPUT = '#settings-stock-default-stock-mode input[type="hidden"]';
    EventHandler.SELECTOR_DEFAULT_STOCK_LEVEL = '#settings-stock-default-stock-level';
    EventHandler.SELECTOR_LOW_STOCK_THRESHOLD_TOGGLE = '#low-stock-threshold-toggle';
    EventHandler.SELECTOR_LOW_STOCK_THRESHOLD_INPUT = '#low-stock-threshold-value';
    EventHandler.SELECTOR_REORDER_QUANTITY_INPUT = '#reorder-quantity';
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

    EventHandler.prototype.listenForLowThresholdToggleChange = function()
    {
        var service = this.getService();
        $(EventHandler.SELECTOR_LOW_STOCK_THRESHOLD_TOGGLE).on('change', function() {
            service.lowStockThresholdChanged($(this).is(':checked'));
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