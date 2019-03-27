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
                    'loadingIndicator':  '/cg-built/zf2-v4-ui/templates/elements/loadingIndicator.mustache',
                    'loading': '/cg-built/products/template/popups/listing/loading.mustache',
                    'popup': '/cg-built/products/template/popups/listing/popup.mustache',
                    'button': '/cg-built/zf2-v4-ui/templates/elements/buttons.mustache',
                    'checkbox': '/cg-built/zf2-v4-ui/templates/elements/checkbox.mustache'
                }
            );
            this.listenToCheckAll();
            this.listenToDownload();

        };
        this.getPopup = function() {
            return popup;
        };
    };

    Service.SELECTOR_REFRESH_BUTTON_SHADOW = '#refresh-button-shadow';

    Service.prototype.listenToCheckAll = function()
    {
        var popup = this.getPopup();
        popup.getElement().on("change", "#listing-download-all", function() {
            popup.getElement().find(":checkbox[name=accounts]:not(:disabled)").prop("checked", this.checked);
            if (this.checked) {
                popup.getElement().find("#listing-download-shadow").removeClass("disabled");
            } else {
                popup.getElement().find("#listing-download-shadow").addClass("disabled");
            }
        });
        popup.getElement().on("change", ":checkbox[name=accounts]:not(:disabled)", function() {
            popup.getElement().find("#listing-download-all").prop(
                "checked",
                (popup.getElement().find(":checkbox[name=accounts]:not(:checked):not(:disabled)").length == 0)
            );
            if (popup.getElement().find(":checkbox[name=accounts]:checked:not(:disabled)").length > 0) {
                popup.getElement().find("#listing-download-shadow").removeClass("disabled");
            } else {
                popup.getElement().find("#listing-download-shadow").addClass("disabled");
            }
        });
    };

    Service.prototype.listenToDownload = function()
    {
        var self = this;
        var popup = self.getPopup();
        popup.getElement().on("click", "#listing-download", function(event) {
            if (popup.getElement().find("#listing-download-shadow.disabled").length == 0) {
                popup.getElement().find("#listing-download-shadow").addClass("disabled");
                self.refresh(
                    popup.getElement().find(":checkbox[name=accounts]:checked:not(:disabled)").map(function() {
                        this.disabled = true;
                        return this.value;
                    }).get()
                );
                if (popup.getElement().find(":checkbox[name=accounts]:not(:disabled)").length == 0) {
                    popup.getElement().find("#listing-download-all").prop("disabled", true);
                }
            }
        });
    };

    Service.prototype.refresh = function(accounts)
    {
        storage.refresh(accounts);
    };

    Service.prototype.displayPopup = function()
    {
        var popup = this.getPopup();
        popup.hide();
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
                    'id': 'listing-download',
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