define([
    './EventHandler.js',
    'ajaxForm'
], function(
    EventHandler,
    AjaxForm
) {
    function Service(notifications)
    {
        var eventHandler;

        this.getNotifications = function()
        {
            return notifications;
        };

        var init = function()
        {
            eventHandler = new EventHandler(this);
            var form = new AjaxForm(notifications, EventHandler.SELECTOR_FORM);
            this.checkInitialStockMode();
        };
        init.call(this);
    }

    Service.STOCK_MODE_ALL = 'all';

    Service.prototype.checkInitialStockMode = function()
    {
        var stockMode = $(EventHandler.SELECTOR_DEFAULT_STOCK_MODE_INPUT).val();
        this.defaultStockModeChanged(stockMode);
    };

    Service.prototype.defaultStockModeChanged = function(stockMode)
    {
        if (stockMode == Service.STOCK_MODE_ALL) {
            $(EventHandler.SELECTOR_DEFAULT_STOCK_LEVEL).attr('disabled', 'disabled').addClass('disabled');
        } else {
            $(EventHandler.SELECTOR_DEFAULT_STOCK_LEVEL).removeAttr('disabled').removeClass('disabled');
        }
    };

    Service.prototype.save = function()
    {
        if (!this.validateForm()) {
            return;
        }
        $(EventHandler.SELECTOR_FORM).submit();
    };

    Service.prototype.validateForm = function()
    {
        var defaultStockMode = $(EventHandler.SELECTOR_DEFAULT_STOCK_MODE_INPUT).val();
        var defaultStockLevel = $(EventHandler.SELECTOR_DEFAULT_STOCK_LEVEL).val();
        if (defaultStockMode != Service.STOCK_MODE_ALL && (parseInt(defaultStockLevel) == NaN || parseInt(defaultStockLevel) < 0)) {
            this.getNotifications().error('Default stock level must be a number >= 0');
            return false;
        }
        return true;
    };

    return Service;
});