define(['../SetupWizard.js'], function(setupWizard) {
    function Payment(notifications)
    {
        var selectedPackage = false;

        this.getNotifications = function()
        {
            return notifications;
        };

        this.getSetupWizard = function()
        {
            return setupWizard;
        };

        this.setSelectedPackage = function(newPackage) {
            selectedPackage = newPackage;
            return this;
        };

        this.getSelectedPackage = function() {
            return selectedPackage;
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
                var selectedPackage = self.getSelectedPackage();
                if (!selectedPackage) {
                    self.getNotifications().error('Please select a package to continue.');
                    reject();
                    return;
                }

                var params = new URLSearchParams(location.search.slice(1));
                if (!$(Payment.SELECTOR_INPUT).val() || !params.get('cardAuth')) {
                    self.getNotifications().error('Please setup a payment method to continue.');
                    reject();
                    return;
                }

                resolve();
            });
        });
    };

    return Payment;
});