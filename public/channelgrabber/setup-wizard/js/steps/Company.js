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
                .removeSubmitButton()
                .listenForVatToggle();
        };
        init.call(this);
    }

    Company.SELECTOR_FORM = '#legalCompanyDetailsForm form';
    Company.SELECTOR_VAT_TOGGLE = '#setup-wizard-company-vat-toggle';
    Company.SELECTOR_VAT_NUMBER = '#setup-wizard-company-vat-number';
    Company.SELECTOR_VAT_NOTICE = '#setup-wizard-company-vat-notice';

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

    Company.prototype.listenForVatToggle = function()
    {
        $(Company.SELECTOR_VAT_TOGGLE).on('change', function()
        {
            var toggle = this;
            if ($(toggle).is(':checked')) {
                $(Company.SELECTOR_VAT_NOTICE + ' .notifications > div').removeClass('error').addClass('success');
                $(Company.SELECTOR_VAT_NOTICE + ' .content').text('Your invoices will show VAT');
                $(Company.SELECTOR_VAT_NUMBER).show();
            } else {
                $(Company.SELECTOR_VAT_NOTICE + ' .notifications > div').removeClass('success').addClass('error');
                $(Company.SELECTOR_VAT_NOTICE + ' .content').text('Your invoices will not show VAT');
                $(Company.SELECTOR_VAT_NUMBER).val('').hide();
            }
        });

        $(Company.SELECTOR_VAT_TOGGLE).trigger('change');

        return this;
    };

    return Company;
});