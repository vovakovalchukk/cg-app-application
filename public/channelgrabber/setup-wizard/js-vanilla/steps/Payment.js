define(['../SetupWizard.js', 'AjaxRequester'], function(setupWizard, ajaxRequester) {
    function Payment(notifications, selectedPackage, selectedBillingDuration, activePaymentMethod, discountCode)
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
            this.rememberSelectedPackage(selectedPackage);
            return this;
        };

        this.getSelectedPackage = function() {
            return selectedPackage;
        };

        this.setSelectedBillingDuration = function(newBillingDuration) {
            selectedBillingDuration = newBillingDuration;
            this.rememberSelectedBillingDuration(selectedBillingDuration);
            return this;
        };

        this.getSelectedBillingDuration = function() {
            return selectedBillingDuration;
        };

        this.getActivePaymentMethod = function() {
            return activePaymentMethod;
        };

        this.getDiscountCode = function() {
            return discountCode;
        };

        var init = function()
        {
            this.registerNextCallback();
        };
        init.call(this);
    }

    Payment.SetPackageUrl = '/setup/payment/setPackage/';
    Payment.RememberSelectedPackageUrl = '/setup/payment/rememberPackage/';
    Payment.RememberSelectedBillingDurationUrl = '/setup/payment/rememberBillingDuration/';

    Payment.prototype.takePayment = function()
    {
        this.getSetupWizard().clickNext();
    };

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

                var selectedBillingDuration = self.getSelectedBillingDuration();
                if (!selectedBillingDuration) {
                    self.getNotifications().error('Please select a billing duration to continue.');
                    reject();
                    return;
                }

                var activePaymentMethod = self.getActivePaymentMethod();
                if (!activePaymentMethod) {
                    self.getNotifications().error('Please setup a payment method to continue.');
                    reject();
                    return;
                }

                self.getNotifications().notice('Setting requested package');
                ajaxRequester.sendRequest(
                    Payment.SetPackageUrl + selectedPackage,
                    {
                        billingDuration: selectedBillingDuration,
                        discountCode: self.getDiscountCode()
                    },
                    function(data) {
                        if (data.success) {
                            resolve();
                        } else {
                            self.getNotifications().error(data.error);
                            reject();
                        }
                    },
                    function(response) {
                        self.getNotifications().ajaxError(response);
                        reject();
                    }
                );
            });
        });
    };

    Payment.prototype.rememberSelectedPackage = function(selectedPackage)
    {
        if (!selectedPackage) {
            return;
        }

        ajaxRequester.sendRequest(
            Payment.RememberSelectedPackageUrl + selectedPackage,
            {},
            function() {},
            function() {}
        );
    };

    Payment.prototype.rememberSelectedBillingDuration = function(selectedBillingDuration)
    {
        if (!selectedBillingDuration) {
            return;
        }

        ajaxRequester.sendRequest(
            Payment.RememberSelectedBillingDurationUrl + selectedBillingDuration,
            {},
            function() {},
            function() {}
        );
    };

    return Payment;
});