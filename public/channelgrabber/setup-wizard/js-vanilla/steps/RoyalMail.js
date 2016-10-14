define([
    '../SetupWizard.js',
    '/cg-built/zf2-netdespatch/js/Setup/Service.js'
], function(
    setupWizard,
    NDService
) {
    function RoyalMail(notifications)
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
            this.registerNextFormSubmission();
        };
        init.call(this);
    }

    RoyalMail.SELECTOR_FORM = '#carrier-account-form';

    RoyalMail.prototype.registerNextFormSubmission = function()
    {
        var self = this;
        this.getSetupWizard().registerNextCallback(function()
        {
            self.getNotifications().notice('Submitting form');
            var ndService = new NDService('Authorisation form submitted');
            return new Promise(function(resolve, reject)
            {
                ndService.save(function(success)
                {
                    if (success) {
                        resolve();
                    } else {
                        reject();
                    }
                });
            });
        });
    };

    return RoyalMail;
});