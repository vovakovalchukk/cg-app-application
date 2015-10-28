define([
    './EventHandler.js',
    'EventCollator',
    'AjaxRequester',
    'DeferredQueue'
], function(
    EventHandler,
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

        this.getEventHandler = function()
        {
            return eventHandler;
        };

        this.getNotifications = function()
        {
            return notifications;
        };

        var init = function()
        {
            eventHandler = new EventHandler(this, eventCollator);
            deferredQueue = new DeferredQueue();
        };
        init.call(this);
    }

    Service.URI_SAVE_ACCOUNTS = '/settings/stock/accounts/save';

    Service.prototype.accountChanged = function(accountId)
    {
        var unique = true;
        $(document).trigger(this.getEventCollator().getRequestMadeEvent(), [
            EventHandler.ACCOUNTS_QUEUE, accountId, unique
        ]);
    };

    Service.prototype.saveAccountSettings = function(accountIds)
    {
        var self = this;
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
                self.getEventHandler().triggerAccountSettingsSavedEvent(data);
            });
        });
    };

    return Service;
});