define(['../SetupWizard.js'], function(setupWizard) {
    function Payment(notifications, selectedPackage, activePaymentMethod)
    {
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

        this.getActivePaymentMethod = function() {
            return activePaymentMethod;
        };

        var init = function()
        {
            this.registerNextCallback();
        };
        init.call(this);
    }

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

                var activePaymentMethod = self.getActivePaymentMethod();
                if (!activePaymentMethod) {
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