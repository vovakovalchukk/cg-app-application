define([
    '../SetupWizard.js',
    'AjaxRequester'
], function(
    setupWizard,
    ajaxRequester
) {
    function Company(notifications)
    {
        this.getSetupWizard = function()
        {
            return setupWizard;
        };

        this.getAjaxRequester = function()
        {
            return ajaxRequester;
        };

        this.getNotifications = function()
        {
            return notifications;
        };

        var init = function()
        {
            this.registerSkipConfirmation()
                .registerNextValidation()
                .removeSubmitButton();
        };
        init.call(this);
    }

    Company.SELECTOR_FORM = '#legalCompanyDetailsForm form';

    Company.prototype.registerSkipConfirmation = function()
    {
        this.getSetupWizard().registerSkipConfirmation(
            'Skipping this step without either a company name or address will mean any invoices/sales receipts that you '
            + 'print for customers will be missing this information.\nAre you sure you wish to skip this step?'
        );
        return this;
    };

    Company.prototype.registerNextValidation = function()
    {
        var self = this;
        this.getSetupWizard().registerNextCallback(function()
        {
            // Need to save the form which involves an asynchronous request so return a Promise
            return new Promise(function(resolve, reject)
            {
                self.saveForm(function(success)
                {
                    if (success) {
                        resolve();
                    } else {
                        reject(new Error('Form could not be saved'));
                    }
                });
            });
        });

        return this;
    };

    Company.prototype.saveForm = function(callback)
    {
        var self = this;
        this.getNotifications().notice('Saving details');

        $(Company.SELECTOR_FORM).ajaxSubmit({
            "dataType": "json",
            "success": function(data) {
                self.getNotifications().success('Your details have been saved');
                callback(true);
            },
            "error": function(request) {
                var message = 'There was a problem saving the form, please try again';
                if (request && request.responseText) {
                    var json = $.parseJSON(request.responseText);
                    if (json.display_exceptions && json.message) {
                        message = json.message;
                    }
                }
                self.getNotifications().error(message);
                callback(false);
            }
        });
    };

    Company.prototype.removeSubmitButton = function()
    {
        $('#company-details-save').closest('.order-inputbox-holder').remove();
        return this;
    };

    return Company;
});