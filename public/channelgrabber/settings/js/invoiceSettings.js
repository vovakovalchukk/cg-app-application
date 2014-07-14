define(function() {
    var InvoiceSettings = function()
    {
        this.successMessage = 'Settings Saved';
        this.errorMessage = 'Error: Settings could not be saved';

        var container = '.invoiceSettings';
        var selector = container + ' .custom-select';
        var defaultSettingsSelector = container + ' .invoiceDefaultSettings #defaultInvoiceCustomSelect input';
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
                'tradingCompanies': getTradingCompanies()
            };
        };

        var getDefault = function()
        {
            return $(defaultSettingsSelector).val();
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
            data: self.getInvoiceSettingsEntity()
        }).success(function() {
            if (n) {
                n.success(self.successMessage);
            }
        }).error(function() {
            if (n) {
                n.error(self.errorMessage);
            }
        });
    };

    return InvoiceSettings
});