define(['popup/mustache'], function(MustachePopup)
{
    function Balance()
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
        }
        init.call(this);
    }

    Balance.SELECTOR_TOPUP_BUTTON = "#top-up-balance-button-shadow";

    Balance.prototype.listenForTopUpClick = function()
    {
        var self = this;
        $(Balance.SELECTOR_TOPUP_BUTTON).click(function(event) {
            event.preventDefault();
            self.getPopup().show(self.getPopupSettings(), 'popup');
        });
    };

    Balance.prototype.getPopupSettings = function()
    {
        var popupSettings = {
            "title": "Buy Postage",
            "accountId": "2",
            "accountBalance": {
                "additionalClass": "popup",
                "currencySymbol": "$",
                "balance": 1000,
                "topUpAmount": 100
            },
            "autoTopUp": {
                "id": "autoTopUp",
                "name": "autoTopUp",
                "selected": true,
                "class": "autoTopUp"
            },
            "tooltip": {
                "id": "autoTopUpTooltip",
                "name": "autoTopUpTooltip",
                "attach": "#topupTooltip",
                "content": "When automatic top-ups are enabled ChannelGrabber will automatically purchase $100 of UPS postage when your balance drops below $100"
            }
        };
        return popupSettings;
    };

    return Balance;
});