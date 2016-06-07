define([
    '../SetupWizard.js',
    'AjaxRequester'
], function(
    setupWizard,
    ajaxRequester
) {
    function Company(notifications, saveUri)
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

        this.getSaveUri = function()
        {
            return saveUri;
        };

        var init = function()
        {
            this.registerSkipConfirmation()
                .registerNextValidation();
        };
        init.call(this);
    }

    Company.SELECTOR_FORM = '#setup-wizard-company-form';

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
            if (!self.isFormValid()) {
                return false;
            }
            // Need to save the form which involves an asynchronous request so return a Promise
            return new Promise(function(resolve, reject)
            {
                self.saveForm(function()
                {
                    self.getNotifications().success('Your details have been saved');
                    //resolve();
reject();
                });
            });
        });

        return this;
    };

    Company.prototype.isFormValid = function()
    {
        var errors = [];
        $(Company.SELECTOR_FORM + ' .required').each(function()
        {
            var input = this;
            if ($(input).val() != '') {
                return true; // continue
            }
            var field = $(input).closest('label').find('.inputbox-label').text().replace(':', '');
            errors.push(field + ' is required');
        });
        if (errors.length == 0) {
            return true;
        }

        var errorMessage = errors.join('<br />');
        this.getNotifications().error(errorMessage);
        return false;
    };

    Company.prototype.saveForm = function(callback)
    {
        this.getNotifications().notice('Saving details');
        var data = {};
        var formArray = $(Company.SELECTOR_FORM).serializeArray();
        for (var index in formArray) {
            var fieldData = formArray[index];
            data[fieldData.name] = fieldData.value;
        }
        this.getAjaxRequester().sendRequest(this.getSaveUri(), data, callback);
    };

    return Company;
});