define(['../SetupWizard.js'], function(setupWizard)
{
    function Channels(notifications, pickChannelUri)
    {
        this.getSetupWizard = function()
        {
            return setupWizard;
        };

        this.getNotifications = function()
        {
            return notifications;
        };

        this.getPickChannelUri = function()
        {
            return pickChannelUri;
        };

        var init = function()
        {
            this.registerNextCallback()
                .hideSkipIfAccountsAdded()
                .listenForAddClick();
        };
        init.call(this);
    }

    Channels.SELECTOR_CHANNEL = '.setup-wizard-account-badge';
    Channels.SELECTOR_SKIP = '.setup-wizard-skip-button';
    Channels.SELECTOR_ADD = '.setup-wizard-account-badges .setup-wizard-button-badge';

    Channels.prototype.registerNextCallback = function()
    {
        this.getSetupWizard().registerNextCallback(function()
        {
            if ($(Channels.SELECTOR_CHANNEL).length == 0) {
                n.error('You must add at least one channel (or you can choose to skip this step)');
                return false;
            }
            return true;
        });

        return this;
    };

    Channels.prototype.hideSkipIfAccountsAdded = function()
    {
        if ($(Channels.SELECTOR_CHANNEL).length == 0) {
            return this;
        }
        // If the user has added accounts then skipping doesnt make sense
        $(Channels.SELECTOR_SKIP).hide();
        return this;
    };

    Channels.prototype.listenForAddClick = function()
    {
        var self = this;
        $(Channels.SELECTOR_ADD).click(function()
        {
            self.showNewChannelOptions();
        });

        return this;
    };

    Channels.prototype.showNewChannelOptions = function()
    {
        window.location = this.getPickChannelUri();
    };

    return Channels;
});