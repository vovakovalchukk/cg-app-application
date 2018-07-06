define(['AjaxRequester'], function(ajaxRequester)
{
    function Settings(config)
    {
        var init = function()
        {
            this.config = config;
            this.attachAutoTopUpListener();
            this.attachTopUpBalanceListener();
        };
        init.call(this);

        this.getAjaxRequester = function()
        {
            return ajaxRequester;
        };

        this.getConfig = function()
        {
            return this.config;
        };
    }

    Settings.LEDGER_AUTO_TOPUP_TOGGLE = "#autoTopUp";
    Settings.LEDGER_TOP_UP_BUTTON = "#topUp";
    Settings.LEDGER_TOPUP_URL = "/settings/channel/shipping/{{accountId}}/ledger/topup"
    Settings.LEDGER_SAVE_URL = "/settings/channel/shipping/{{accountId}}/ledger/save"

    Settings.prototype.attachAutoTopUpListener = function()
    {
        $(Settings.LEDGER_AUTO_TOPUP_TOGGLE).on("change", this.sendAjaxAutoTopUp.bind(this));
    }

    Settings.prototype.sendAjaxAutoTopUp = function()
    {
        var url = Settings.LEDGER_SAVE_URL.replace("{{accountId}}", this.getConfig().accountId);
        var ajaxRequester = this.getAjaxRequester();
        ajaxRequester.getNotificationHandler().notice('Saving preferences', true);
        return ajaxRequester.sendRequest(
            url,
            {autoTopUp: $(Settings.LEDGER_AUTO_TOPUP_TOGGLE).prop("checked")}
        );
    }

    Settings.prototype.attachTopUpBalanceListener = function()
    {
        $(Settings.LEDGER_TOP_UP_BUTTON).on("click", this.sendAjaxBalanceTopUp.bind(this));
    }

    Settings.prototype.sendAjaxBalanceTopUp = function()
    {
        var url = Settings.LEDGER_TOPUP_URL.replace("{{accountId}}", this.getConfig().accountId);
        var ajaxRequester = this.getAjaxRequester();
        ajaxRequester.getNotificationHandler().notice('Topping up balance', true);
        return ajaxRequester.sendRequest(
            url,
            {},
            function(data) {
                location.reload();
            }
        );
    }
    return Settings;
});