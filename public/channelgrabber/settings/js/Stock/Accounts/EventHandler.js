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
            this.listenForAccountsToggleChange()
                .listenForAccountSettingsTimeout();
        };
        init.call(this);
    }

    EventHandler.ACCOUNTS_QUEUE = 'StockSettingsAccounts';
    EventHandler.SELECTOR_ACCOUNTS_TABLE = '#accounts-table';
    EventHandler.SELECTOR_ACCOUNT_TOGGLE = '#accounts-table input[type="checkbox"]';
    EventHandler.EVENT_ACCOUNT_SETTINGS_SAVED = 'account-stock-settings-saved';

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

    EventHandler.prototype.triggerAccountSettingsSavedEvent = function(data)
    {
        $(document).trigger(EventHandler.EVENT_ACCOUNT_SETTINGS_SAVED, [data]);
        return this;
    };

    return EventHandler;
});