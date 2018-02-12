define([
    'Listing/Import/Ajax',
    'popup/mustache'
], function (
    storage,
    MustachePopup
) {
    var Service = function() {
        var popup;
        this.init = function() {
            popup = new MustachePopup(
                {
                    'header': '/cg-built/products/template/popups/listing/header.mustache',
                    'loading': '/cg-built/products/template/popups/listing/loading.mustache',
                    'popup': '/cg-built/products/template/popups/listing/popup.mustache',
                    'button': '/cg-built/zf2-v4-ui/templates/elements/buttons.mustache',
                    'checkbox': '/cg-built/zf2-v4-ui/templates/elements/checkbox.mustache'
                }
            );
            this.listenToCheckAll();

        };
        this.getPopup = function () {
            return popup;
        };
    };

    Service.SELECTOR_REFRESH_BUTTON_SHADOW = '#refresh-button-shadow';

    Service.prototype.listenToCheckAll = function()
    {
        var popup = this.getPopup();
        popup.getElement().on("change", "#listing-download-all", function() {
            popup.getElement().find(":checkbox[name=accounts]:not(:disabled)").prop("checked", this.checked);
        });
        popup.getElement().on("change", ":checkbox[name=accounts]:not(:disabled)", function() {
            popup.getElement().find("#listing-download-all").prop(
                "checked",
                (popup.getElement().find(":checkbox[name=accounts]:not(:checked):not(:disabled)").length == 0)
            );
        });
    };

    Service.prototype.refresh = function(accounts)
    {
        storage.refresh(accounts);
    };

    Service.prototype.displayPopup = function()
    {
        var popup = this.getPopup();
        popup.show(null, 'loading');
        storage.refreshDetails(function(details) {
            details.hasAccounts = false;
            details.account = [];
            var checkAllEnabled = false;
            for (var accountId in details['accounts']) {
                if (details['accounts'].hasOwnProperty(accountId)) {
                    details.hasAccounts = true;
                    details.account.push(Object.assign(
                        {},
                        details['accounts'][accountId],
                        {'checkbox': {
                            'id': 'listing-download-' + accountId,
                            'name': 'accounts',
                            'value': accountId,
                            'disabled': !details['accounts'][accountId]['refreshAllowed']
                        }}
                    ));
                    if (details['accounts'][accountId]['refreshAllowed']) {
                        checkAllEnabled = true;
                    }
                }
            }
            details.download = {
                'buttons': {
                    'class': 'listingDownload',
                    'title': 'Download Listings',
                    'disabled': true
                }
            };
            details.checkall = {
                'id': 'listing-download-all',
                'disabled': !checkAllEnabled
            };
            popup.hide();
            popup.show(details, 'popup');
        });
    };

    return new Service();
});