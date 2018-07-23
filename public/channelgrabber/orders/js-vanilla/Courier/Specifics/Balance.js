define([
    'popup/mustache',
    'AjaxRequester',
], function(
    MustachePopup,
    ajaxRequester
) {
    function Balance(accountId)
    {
        var popup;
        var init = function()
        {
            popup = new MustachePopup(
                {
                    'popup': '/cg-built/shipstation/template/accountTopUp.mustache',
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

        this.getAccountId = function()
        {
            return accountId;
        };

        this.getAjaxRequester = function()
        {
            return ajaxRequester;
        };

        init.call(this);
    }

    Balance.SELECTOR_TOPUP_BUTTON = "#top-up-balance-button-shadow";
    Balance.FETCH_ACCOUNT_BALANCE_URL = '/orders/courier/specifics/{{accountId}}/fetchAccountBalance';

    Balance.prototype.listenForTopUpClick = function()
    {
        var self = this;
        $(Balance.SELECTOR_TOPUP_BUTTON).click(function(event) {
            $(this).addClass('disabled');
            event.preventDefault();
            self.renderPopup();
        });
    };

    Balance.prototype.getShippingLedgerDetails = function()
    {
        var self = this;
        var data = {
            "account": this.getAccountId()
        };

        var uri = Balance.FETCH_ACCOUNT_BALANCE_URL.replace('{{accountId}}', data.account);

        this.getAjaxRequester().sendRequest(uri, data, self.showPopup, self.fail, self);
    };

    Balance.prototype.renderPopup = function()
    {
        this.getShippingLedgerDetails();
    }

    Balance.prototype.showPopup = function(data)
    {
        console.log(data);
        var popupSettings = {
            "additionalClass": "popup",
            "title": "Buy Postage",
            "accountId": this.getAccountId(),
            "accountBalance": {
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
        this.getPopup().show(popupSettings, 'popup');
        $(Balance.SELECTOR_TOPUP_BUTTON).removeClass('disabled');
    }

    Balance.fail = function(data)
    {
        $(Balance.SELECTOR_TOPUP_BUTTON).removeClass('disabled');
    }

    return Balance;
});