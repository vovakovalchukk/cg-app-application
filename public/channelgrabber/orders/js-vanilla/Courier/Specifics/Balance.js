define(['popup/mustache'], function(MustachePopup)
{
    function Balance()
    {
        var popup;
        var init = function() {
            popup = new MustachePopup('/channelgrabber/orders/template/courier/popup/accountTopUp.mustache');
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
            self.getPopup().show();
        });
    };

    return Balance;
});