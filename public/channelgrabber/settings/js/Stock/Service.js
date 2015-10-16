define([
    './EventHandler.js',
    'ajaxForm',
    'EventCollator',
    'AjaxRequester',
    'DeferredQueue'
], function(
    EventHandler,
    AjaxForm,
    eventCollator,
    ajaxRequester,
    DeferredQueue
) {
    function Service(notifications)
    {
        var eventHandler;
        var deferredQueue;

        this.getEventCollator = function()
        {
            return eventCollator;
        };

        this.getAjaxRequester = function()
        {
            return ajaxRequester;
        };

        this.getDeferredQueue = function()
        {
            return deferredQueue;
        };

        this.getNotifications = function()
        {
            return notifications;
        };

        var init = function()
        {
            eventHandler = new EventHandler(this, eventCollator);
            deferredQueue = new DeferredQueue();
            var form = new AjaxForm(notifications, EventHandler.SELECTOR_FORM);
            this.checkInitialStockMode();
        };
        init.call(this);
    }

    Service.STOCK_MODE_ALL = 'all';
    Service.URI_SAVE_ACCOUNTS = '/settings/stock/accounts/save';

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

    Service.prototype.accountChanged = function(accountId)
    {
        var unique = true;
        $(document).trigger(this.getEventCollator().getRequestMadeEvent(), [
            EventHandler.ACCOUNTS_QUEUE, accountId, unique
        ]);
    };

    Service.prototype.saveAccountSettings = function(accountIds)
    {
        var notifications = this.getNotifications();
        notifications.notice('Saving account settings');
        var data = {};
        for (var count in accountIds) {
            var accountId = accountIds[count];
            $(EventHandler.SELECTOR_ACCOUNTS_TABLE + ' input[name^="account['+accountId+']"]').each(function()
            {
                var element = this;
                var name = $(element).attr('name');
                var value = $(element).val();
                if ($(element).attr('type') == 'checkbox' ) {
                    value = ($(element).is(':checked') ? 1 : 0);
                }
                data[name] = value;
            });
        }
        var ajaxRequester = this.getAjaxRequester();
        this.getDeferredQueue().queue(function() {
            return ajaxRequester.sendRequest(Service.URI_SAVE_ACCOUNTS, data, function()
            {
                notifications.success('Changes saved successfully');
            });
        });
    };

    return Service;
});