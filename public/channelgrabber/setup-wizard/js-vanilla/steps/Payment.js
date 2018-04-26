define(['../SetupWizard.js'], function(setupWizard) {
    function Payment(notifications)
    {
        this.getNotifications = function()
        {
            return notifications;
        };

        this.getSetupWizard = function()
        {
            return setupWizard;
        };

        var init = function()
        {
            this.registerNextCallback();
        };
        init.call(this);
    }

    Payment.SELECTOR_INPUT = '#billingPaymentMethod input[name=billingPaymentMethod]';

    Payment.prototype.registerNextCallback = function()
    {
        var self = this;
        this.getSetupWizard().registerNextCallback(function()
        {
            return new Promise(function(resolve, reject)
            {
                var params = new URLSearchParams(location.search.slice(1));
                if ($(Payment.SELECTOR_INPUT).val() && params.get('cardAuth')) {
                    resolve();
                } else {
                    self.getNotifications().error('Please setup a payment method to continue.');
                    reject();
                }
            });
        });
    };

    return Payment;
});