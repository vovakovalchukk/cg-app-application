define(
    ["popup/confirm","cg-mustache"], function (Confirm, CGMustache){
    var InvoiceSettings = function(hasAmazonAccount)
    {
        this.successMessage = 'Settings Saved';
        this.errorMessage = 'Error: Settings could not be saved';

        var container = '.invoiceSettings';
        var selector = container + ' .custom-select, ' + container + ' input:checkbox';
        var verifyEmailSelector = container + ' .invoiceDefaultSettings button#verifyEmail';
        var defaultSettingsSelector = container + ' .invoiceDefaultSettings #defaultInvoiceCustomSelect input';
        var autoEmailSettingsSelector = container + ' .invoiceDefaultSettings #autoEmail';
        var emailSendAsSelector = container + ' .invoiceDefaultSettings #emailSendAs';
        var productImagesSettingsSelector = container + ' .invoiceDefaultSettings #productImages';
        var tradingCompaniesSelector = container + ' .invoiceTradingCompanySettings input.invoiceTradingCompaniesCustomSelect';

        var emailInvoiceFields = $(container + ' .emailInvoiceFields');

        var init = function(){
            var self = this;

            if ($('#autoEmail').prop('checked')) {
                emailInvoiceFields.removeClass('hidden');
            }

            $(document).on('change', selector, function (){
                if (this.id == "autoEmail" && getElementOnClickCheckedStatus(this.id) && hasAmazonAccount == true){
                    showConfirmationMessageForAmazonAccount(self);
                } else {
                    // ajaxSave(self);
                }
            });

            // $(document).on('click', verifyEmailSelector, function (){
            //     ajaxSave(self);
            // });

            $(document).on('click', autoEmailSettingsSelector, function() {
                toggleEmailInvoiceFields();
            });

        };
        
        function showConfirmationMessageForAmazonAccount(self){
            var templateUrlMap = {
                message: '/cg-built/settings/template/Warnings/amazonEmailWarning.mustache'
            };

            CGMustache.get().fetchTemplates(templateUrlMap, function (templates, cgmustache){
               var messageHTML = cgmustache.renderTemplate(templates, {}, "message");
               var confirm = new Confirm(messageHTML, function (response) {
                    if (response == "Yes"){
                        $('#autoEmail').attr('checked', true);
                    }
                    if (response == "No"){
                        $('#autoEmail').click();
                    }
                });
            });
        }

        function ajaxSave(object) {
            object.save();
        }

        function getElementOnClickCheckedStatus(elementID) {
            if ($('#' + elementID).is(":checked")) {
                return true;
            }
            return false;
        }

        function toggleEmailInvoiceFields()
        {
            emailInvoiceFields.toggleClass('hidden');
        }

        this.getInvoiceSettingsEntity = function()
        {
            return {
                'default': getDefault(),
                'autoEmail': getAutoEmail(),
                'emailSendAs': getEmailSendAs(),
                'productImages': getProductImages(),
                'tradingCompanies': getTradingCompanies(),
                'eTag': $('#setting-etag').val()
            };
        };

        var getDefault = function()
        {
            return $(defaultSettingsSelector).val();
        };

        var getAutoEmail = function()
        {
            return $(autoEmailSettingsSelector).is(':checked');
        };

        var getEmailSendAs = function()
        {
            return $(emailSendAsSelector).val();
        }

        var getProductImages = function()
        {
            return $(productImagesSettingsSelector).is(':checked');
        };

        var getTradingCompanies = function()
        {
            var tradingCompanies = {};
            $(tradingCompaniesSelector).each(function(){
                var assignedInvoice = $(this).val();
                var tradingCompanyId = $(this).attr('name').replace('invoiceTradingCompaniesCustomSelect_', '');
                tradingCompanies[tradingCompanyId] = assignedInvoice;
            });
            return tradingCompanies;
        };

        init.call(this);
    };

    InvoiceSettings.prototype.save = function()
    {
        var self = this;
        $.ajax({
            url: "mapping/save",
            type: "POST",
            dataType: 'json',
            data: self.getInvoiceSettingsEntity()
        }).success(function(data) {
            $('#setting-etag').val(data.eTag);
            if (n) {
                n.success(self.successMessage);
            }
        }).error(function(error, textStatus, errorThrown) {
            if (n) {
                n.ajaxError(error, textStatus, errorThrown);
            }
        });
    };

    return InvoiceSettings
});
