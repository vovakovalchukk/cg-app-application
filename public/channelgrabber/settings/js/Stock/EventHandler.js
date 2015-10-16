define([], function()
{
    function EventHandler(service, eventCollator)
    {
        this.getService = function()
        {
            return service;
        };

        this.getEventCollator = function()
        {
            return eventCollator;
        };

        var init = function()
        {
            this.listenForDefaultStockModeChange()
                .listenForSaveButtonClick()
                .listenForAccountsToggleChange()
                .listenForAccountSettingsTimeout();
        };
        init.call(this);
    }

    EventHandler.ACCOUNTS_QUEUE = 'StockSettingsAccounts';
    EventHandler.SELECTOR_DEFAULT_STOCK_MODE_SELECT = '#settings-stock-default-stock-mode';
    EventHandler.SELECTOR_DEFAULT_STOCK_MODE_INPUT = '#settings-stock-default-stock-mode input[type="hidden"]';
    EventHandler.SELECTOR_DEFAULT_STOCK_LEVEL = '#settings-stock-default-stock-level';
    EventHandler.SELECTOR_SAVE_BUTTON = '#settings-stock-save-button';
    EventHandler.SELECTOR_FORM = '#settings-stock-form';
    EventHandler.SELECTOR_ACCOUNTS_TABLE = '#accounts-table';
    EventHandler.SELECTOR_ACCOUNT_TOGGLE = '#accounts-table input[type="checkbox"]';

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

    EventHandler.prototype.listenForAccountsToggleChange = function()
    {
        var service = this.getService();
        $(document).on('change', EventHandler.SELECTOR_ACCOUNT_TOGGLE, function()
        {
            var element = this;
            var accountId = $(element).attr('id').split('_').pop();
            service.accountChanged(accountId);
        });
        return this;
    };


    EventHandler.prototype.listenForAccountSettingsTimeout = function()
    {
        var service = this.getService();
        $(document).on(this.getEventCollator().getQueueTimeoutEventPrefix() + EventHandler.ACCOUNTS_QUEUE, function(event, data) {
            service.saveAccountSettings(data);
        });
        return this;
    };

    return EventHandler;
});