define(function() {
    var InvoiceSettings = function(id)
    {
        var id;
        var container = '.invoiceSettings';
        var selector = container + ' select';
        var defaultSettingsSelector = container + ' .invoiceDefaultSettings select';
        var tradingCompaniesSelector = container + ' .invoiceTradingCompanySettings select';

        var successMessage = 'Settings Saved';
        var errorMessage = 'Error: Settings could not be saved';

        var init = function()
        {;
            $(document).on('change', selector, function() {
                save();
            });
        }

        var save = function()
        {
            $.ajax({
                url: "mapping/save",
                type: "POST",
                data: getInvoiceSettingsEntity()
            }).success(function() {
                if (n) {
                    n.success(successMessage);
                }
            }).error(function() {
                if (n) {
                    n.error(errorMessage);
                }
            });
        }

        var getInvoiceSettingsEntity = function()
        {
            return {
                'id': getId(),
                'default': getDefault(),
                'tradingCompanies': getTradingCompanies()
            };
        }

        var getId = function()
        {
            return id;
        };

        var getDefault = function()
        {
            return $(defaultSettingsSelector).val();
        }

        var getTradingCompanies = function()
        {
            var tradingCompanies = {};
            $(tradingCompaniesSelector).each(function(){
                var tradingCompanyId = $(this).data('trading-company');
                var assignedInvoice = $(this).val();

                tradingCompanies[tradingCompanyId] = assignedInvoice;
            });
            return tradingCompanies;
        }

        init();
    };

    InvoiceSettings.init = function(id)
    {
        return new InvoiceSettings(id);
    }

    return InvoiceSettings
});