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
                .registerNextValidation()
                .listenForManualEntryToggle()
                .listenForSearchSelection()
                .listenForVatToggle();
        };
        init.call(this);
    }

    Company.SELECTOR_FORM = '#setup-wizard-company-form';
    Company.SELECTOR_TOGGLE = '.setup-wizard-company-address-toggle a';
    Company.SELECTOR_SEARCH = '#setup-wizard-company-address-search';
    Company.SELECTOR_ADDRESS = '#setup-wizard-company-address-fields';
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

    Company.prototype.listenForManualEntryToggle = function()
    {
        var self = this;
        $(Company.SELECTOR_TOGGLE).click(function()
        {
            self.toggleAddressFields();
        });

        if ($(Company.SELECTOR_FORM + ' input[name="address[address1]"').val() &&
            $(Company.SELECTOR_FORM + ' input[name="address[addressPostcode]"').val()
        ) {
            self.toggleAddressFields();
        }

        return this;
    };

    Company.prototype.toggleAddressFields = function()
    {
        if ($(Company.SELECTOR_SEARCH).is(':visible')) {
            $(Company.SELECTOR_SEARCH).hide();
            $(Company.SELECTOR_ADDRESS).show();
        } else {
            $(Company.SELECTOR_SEARCH).show();
            $(Company.SELECTOR_ADDRESS).hide();
        }
    };

    Company.prototype.listenForSearchSelection = function()
    {
        var self = this;
        $(Company.SELECTOR_SEARCH).on('select', function()
        {
            self.toggleAddressFields();
        });

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