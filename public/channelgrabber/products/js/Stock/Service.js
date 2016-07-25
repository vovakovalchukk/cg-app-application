define([
    'Stock/DomListener',
    'Product/Storage/Ajax',
    'DeferredQueue',
    'popup/generic'
], function (
    DomListener,
    storage,
    DeferredQueue,
    Popup
) {
    var Service = function(accountStockModesEnabled)
    {
        var domListener;
        var deferredQueue;
        var accountsPopup;

        this.getStorage = function()
        {
            return storage;
        };

        this.getDomListener = function()
        {
            return domListener;
        };

        this.getDeferredQueue = function()
        {
            return deferredQueue;
        };

        this.getAccountsPopup = function()
        {
            return accountsPopup;
        };

        this.setAccountsPopup = function(newAccountsPopup)
        {
            accountsPopup = newAccountsPopup;
            return this;
        };

        this.getAccountStockModesEnabled = function()
        {
            return accountStockModesEnabled;
        };

        this.setAccountStockModesEnabled = function(newAccountStockModesEnabled)
        {
            accountStockModesEnabled = newAccountStockModesEnabled;
            return this;
        };

        var init = function()
        {
            domListener = new DomListener(this);
            deferredQueue = new DeferredQueue();
            // Prevent account settings table loading until we need it
            $('#accounts-table').one('fnPreDrawCallback', function() { return false; });
        };
        init.call(this);
    };

    Service.MIN_HTTP_CODE_ERROR = 400;
    Service.SELECTOR_STOCK_TABLE = '.stock-table';
    Service.SELECTOR_STOCK_ROW_PREFIX = '#stock-row-';
    Service.SELECTOR_ACCOUNT_SETTINGS = '#account-stock-settings-table-container';
    Service.ACCOUNT_POPUP_WIDTH_PX = 620;
    Service.ACCOUNT_POPUP_HEIGHT_PX = 300;
    Service.ACCOUNT_POPUP_TABLE_HEIGHT_PX = 185;

    Service.prototype.save = function(stockLocationId, totalQuantity, eTag, eTagCallback)
    {
        n.notice('Saving stock total');
        $.ajax({
            url: 'products/stock/update',
            type: 'POST',
            dataType : 'json',
            data: {
                'stockLocationId': stockLocationId,
                'totalQuantity': totalQuantity,
                'eTag': eTag
            },
            success: function(data) {
                if (data.eTag) {
                    eTagCallback(data.eTag);
                    n.success('Stock was updated successfully');
                    return;
                }
                if (data.message) {
                    if (data.code && parseInt(data.code) < Service.MIN_HTTP_CODE_ERROR) {
                        n.success(data.message);
                        return;
                    }

                    n.error(data.message);
                    return;
                }
                n.error('An unknown error occurred');
            },
            error: function(error, textStatus, errorThrown) {
                n.ajaxError(error, textStatus, errorThrown);
            }
        });
    };

    Service.prototype.saveStockLevel = function(productId, stockLevel)
    {
        if (parseInt(stockLevel) == NaN || parseInt(stockLevel) < 0) {
            n.error('Stock level must be a number greater than or equal to zero.');
            return;
        }
        n.notice('Saving stock level');
        var self = this;
        this.getDeferredQueue().queue(function() {
            return self.getStorage().saveStockLevel(productId, stockLevel, function(response) {
                for (var id in response.affectedProducts) {
                    $('#product-stock-level-'+id).val(stockLevel);
                }
                n.success('Product stock level updated successfully');
            });
        });
    };

    Service.prototype.saveStockModeForProduct = function(productId, value, eTagCallback)
    {
        n.notice('Saving stock mode');
        var self = this;
        this.getDeferredQueue().queue(function() {
            return self.getStorage().saveStockMode(productId, value, function(response) {
                var stockMode = (value !== 'null' ? value : null);
                self.checkStockModeAgainstAccountSettings(stockMode);
                self.getDomListener().triggerStockModeUpdatedEvent(productId, stockMode, response.stockModeDefault, response.stockModeDesc, response.stockLevel);
                n.success('Product stock mode updated successfully');
            });
        });
    };

    Service.prototype.checkStockModeAgainstAccountSettings = function(stockMode)
    {
        if (stockMode == null) {
            return;
        }
        var accountStockModesEnabled = this.getAccountStockModesEnabled();
        if (accountStockModesEnabled[stockMode]) {
            return;
        }
        this.showAccountSettingsPopup(stockMode);
    };

    Service.prototype.showAccountSettingsPopup = function(stockMode)
    {
        if (this.getAccountsPopup()) {
            this.getAccountsPopup().getElement().find('.stock-mode').text(stockMode);
            this.getAccountsPopup().show();
            return;
        }

        var popup = new Popup('', Service.ACCOUNT_POPUP_WIDTH_PX, Service.ACCOUNT_POPUP_HEIGHT_PX);
        this.setAccountsPopup(popup);
        popup.htmlContent('<p>Before you can use <span class="stock-mode">' + stockMode + '</span> stock levels you\'ll need to enable them on at least one sales channel:</p>');
        $(Service.SELECTOR_ACCOUNT_SETTINGS + ' .dataTables_wrapper').removeClass('scroll-height-auto');
        $(Service.SELECTOR_ACCOUNT_SETTINGS + ' .dataTables_scrollBody').css('height', Service.ACCOUNT_POPUP_TABLE_HEIGHT_PX+'px');
        popup.getElement().append($(Service.SELECTOR_ACCOUNT_SETTINGS).show());
        popup.getElement().append('<p>If you want to change these later go to the <em>Settings -> Product Management -> Stock</em> settings page</p>');
        popup.show();
        $('#accounts-table').cgDataTable('redraw');
    };

    Service.prototype.accountStockSettingsChanged = function(settings)
    {
        var accountStockModesEnabled = this.getAccountStockModesEnabled();
        for (var name in settings) {
            if (name.match(/fixed/i) && settings[name]) {
                accountStockModesEnabled.fixed = true;
            } else if (name.match(/max/i) && settings[name]) {
                accountStockModesEnabled.max = true;
            }
        }
        this.setAccountStockModesEnabled(accountStockModesEnabled);
    };

    return Service;
});
