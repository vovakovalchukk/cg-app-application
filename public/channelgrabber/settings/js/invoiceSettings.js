define(function() {
    var InvoiceSettings = function()
    {
        this.successMessage = 'Settings Saved';
        this.errorMessage = 'Error: Settings could not be saved';

        var container = '.invoiceSettings';
        var selector = container + ' .custom-select, ' + container + ' input:checkbox';
        var defaultSettingsSelector = container + ' .invoiceDefaultSettings #defaultInvoiceCustomSelect input';
        var autoEmailSettingsSelector = container + ' .invoiceDefaultSettings #autoEmail';
        var tradingCompaniesSelector = container + ' .invoiceTradingCompanySettings input.invoiceTradingCompaniesCustomSelect';

        var init = function() {
            var self = this;
            $(document).on('change', selector, function () {
                self.save();
            });
        };

        this.getInvoiceSettingsEntity = function()
        {
            return {
                'default': getDefault(),
                'autoEmail': getAutoEmail(),
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

        var getTradingCompanies = function()
        {
            var tradingCompanies = {};
            $(tradingCompaniesSelector).each(function(){
                var assignedInvoice = $(this).val();
                var tradingCompanyId = $(this).attr('name').replace('invoiceTradingCompaniesCustomSelect_','');
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
            dataType : 'json',
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