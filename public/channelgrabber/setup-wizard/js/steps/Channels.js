define(['../SetupWizard.js'], function(setupWizard)
{
    function Channels(notifications)
    {
        this.getSetupWizard = function()
        {
            return setupWizard;
        };

        this.getNotifications = function()
        {
            return notifications;
        };

        var init = function()
        {
            this.registerNextCallback()
                .hideSkipIfAccountsAdded();
        };
        init.call(this);
    }

    Channels.SELECTOR_CHANNEL = '.setup-wizard-account-badge';
    Channels.SELECTOR_SKIP = '.setup-wizard-skip-button';

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
            return;
        }
        // If the user has added accounts then skipping doesnt make sense
        $(Channels.SELECTOR_SKIP).hide();
        return this;
    };

    return Channels;
});