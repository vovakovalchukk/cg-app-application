define(
    ["popup/confirm","cg-mustache"], function (Confirm, CGMustache){
    var InvoiceSettings = function(hasAmazonAccount)
    {
        this.successMessage = 'Settings Saved';
        this.errorMessage = 'Error: Settings could not be saved';

        var container = '.invoiceSettings';
        var selector = container + ' .custom-select, ' + container + ' input:checkbox';
        var defaultSettingsSelector = container + ' .invoiceDefaultSettings #defaultInvoiceCustomSelect input';
        var autoEmailSettingsSelector = container + ' .invoiceDefaultSettings #autoEmail';
        var productImagesSettingsSelector = container + ' .invoiceDefaultSettings #productImages';
        var tradingCompaniesSelector = container + ' .invoiceTradingCompanySettings input.invoiceTradingCompaniesCustomSelect';

        var init = function(){
            var self = this;
            $(document).on('change', selector, function (){
                if (this.id == "autoEmail" && getElementOnClickCheckedStatus(this.id) && hasAmazonAccount == true){
                    showConfirmationMessageForAmazonAccount(self);
                } else {
                    ajaxSave(self);
                }
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
                        ajaxSave(self);
                    }
                    if (response == "No"){
                        $('#autoEmail').attr('checked', false);
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

        this.getInvoiceSettingsEntity = function()
        {
            return {
                'default': getDefault(),
                'autoEmail': getAutoEmail(),
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
