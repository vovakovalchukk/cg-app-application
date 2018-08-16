define([
    'popup/mustache',
    'AjaxRequester',
], function(
    MustachePopup,
    ajaxRequester
) {
    function Balance(organisationUnitId, courierAccountId)
    {
        var popup;
        var init = function()
        {
            popup = new MustachePopup(
                {
                    'popup': '/cg-built/shipstation/template/shippingLedgerTopUp.mustache',
                    'elements/toggle.mustache': '/cg-built/zf2-v4-ui/templates/elements/toggle.mustache',
                    'elements/tooltip.mustache': '/cg-built/zf2-v4-ui/templates/elements/tooltip.mustache'
                }
            );
            this.listenForTopUpClick();
        };

        this.getPopup = function()
        {
            return popup;
        };

        this.getOrganisationUnitId = function()
        {
            return organisationUnitId;
        };

        this.getCourierAccountId = function()
        {
            return courierAccountId;
        }

        this.getAjaxRequester = function()
        {
            return ajaxRequester;
        };

        init.call(this);
    }

    Balance.SELECTOR_TOPUP_BUTTON = "#top-up-balance-button-shadow";
    Balance.FETCH_SHIPPING_LEDGER_BALANCE_URL = '/orders/courier/specifics/{{courierAccountId}}/fetchShippingLedgerBalance';

    Balance.prototype.listenForTopUpClick = function()
    {
        var self = this;
        $(Balance.SELECTOR_TOPUP_BUTTON).click(function(event) {
            $(this).addClass('disabled');
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
            self.renderPopup();
        });
    };

    Balance.prototype.getShippingLedgerDetails = function()
    {
        var data = {
            "organisationUnitId": this.getOrganisationUnitId()
        };
        var uri = Balance.FETCH_SHIPPING_LEDGER_BALANCE_URL.replace('{{courierAccountId}}', this.getCourierAccountId());
        var ajaxRequester = this.getAjaxRequester();
        return new Promise(function(resolve, reject) {
            ajaxRequester.sendRequest(
                uri,
                data,
                function (data) {
                    if (data.success === false) {
                        reject(data);
                    } else {
                        resolve(data);
                    }
                }
            );
        });
    };

    Balance.prototype.renderPopup = function(additionalPopupSettings)
    {
        var self = this;
        var promise = this.getShippingLedgerDetails();
        promise.then(function(data) {
            self.showPopup(data, additionalPopupSettings);
        }).catch(function(data) {
            self.fail(data);
        });
    };

    Balance.prototype.showPopup = function(data, additionalPopupSettings)
    {
        additionalPopupSettings = additionalPopupSettings || {};
        var popupSettings = {
            "additionalClass": "popup",
            "title": "Buy Postage",
            "accountId": this.getCourierAccountId(),
            "shippingLedgerBalance": {
                "currencySymbol": "$",
                "balance": data.shippingLedger.balance,
                "topUpAmount": 100
            },
            "autoTopUp": {
                "id": "autoTopUp",
                "name": "autoTopUp",
                "selected": data.shippingLedger.autoTopUp,
                "class": "autoTopUp"
            },
            "tooltip": {
                "id": "autoTopUpTooltip",
                "name": "autoTopUpTooltip",
                "attach": "#topupTooltip",
                "content": "When automatic top-ups are enabled ChannelGrabber will automatically purchase $100 of UPS postage when your balance drops below $100"
            }
        };
        var settings = Object.assign(popupSettings, additionalPopupSettings);
        this.getPopup().show(settings, 'popup');
        $(Balance.SELECTOR_TOPUP_BUTTON).removeClass('disabled');
    };

    Balance.prototype.fail = function(data)
    {
        $(Balance.SELECTOR_TOPUP_BUTTON).removeClass('disabled');
    };

    return Balance;
});