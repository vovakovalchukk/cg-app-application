define(function() {
    var InvoiceSettings = function()
    {
        this.successMessage = 'Settings Saved';
        this.errorMessage = 'Error: Settings could not be saved';

        var container = '.invoiceSettings';
        var selector = container + ' select';
        var defaultSettingsSelector = container + ' .invoiceDefaultSettings select';
        var tradingCompaniesSelector = container + ' .invoiceTradingCompanySettings select';

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
                var tradingCompanyId = $(this).data('trading-company');
                var assignedInvoice = $(this).val();

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