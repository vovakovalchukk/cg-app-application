define(['AjaxRequester'], function(ajaxRequester)
{
    function Settings(config)
    {
        var init = function()
        {
            this.config = config;
            this.attachAutoTopUpListener();
            this.attachTopUpBalanceListener();
            this.createLabelButtonToClick = undefined;
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

        this.setCreateLabelButtonToClick = function(createLabelButtonToClick)
        {
            this.createLabelButtonToClick = createLabelButtonToClick;
        };
    }

    Settings.LEDGER_AUTO_TOPUP_TOGGLE = "#autoTopUp";
    Settings.LEDGER_TOP_UP_BUTTON = "#topUp";
    Settings.LEDGER_TOPUP_URL = "/settings/channel/shipping/{{accountId}}/ledger/topup";
    Settings.LEDGER_SAVE_URL = "/settings/channel/shipping/{{accountId}}/ledger/save";

    Settings.prototype.attachAutoTopUpListener = function()
    {
        $(Settings.LEDGER_AUTO_TOPUP_TOGGLE).on("change", this.sendAjaxAutoTopUp.bind(this));
    };

    Settings.prototype.sendAjaxAutoTopUp = function()
    {
        var url = Settings.LEDGER_SAVE_URL.replace("{{accountId}}", this.getConfig().accountId);
        var ajaxRequester = this.getAjaxRequester();
        ajaxRequester.getNotificationHandler().notice('Saving preferences', true);
        return ajaxRequester.sendRequest(
            url,
            {autoTopUp: $(Settings.LEDGER_AUTO_TOPUP_TOGGLE).prop("checked")}
        );
    };

    Settings.prototype.attachTopUpBalanceListener = function()
    {
        var self = this;
        $(Settings.LEDGER_TOP_UP_BUTTON).on("click", function() {
            var promise = self.sendAjaxBalanceTopUp();
            promise.then(function(data) {
                self.handleBalanceSuccess(data);
            }).catch(function(data) {
                self.getAjaxRequester().handleFailure(data);
            });
        });
    };

    Settings.prototype.sendAjaxBalanceTopUp = function()
    {
        var url = Settings.LEDGER_TOPUP_URL.replace("{{accountId}}", this.getConfig().accountId);
        var ajaxRequester = this.getAjaxRequester();
        return new Promise(function(resolve, reject) {

            ajaxRequester.getNotificationHandler().notice('Topping up balance', true);
            ajaxRequester.sendRequest(
                url,
                {},
                function(data) {
                    if (data.success === false) {
                        reject(data);
                    } else {
                        resolve(data);
                    }
                }
            );
        });
    };

    Settings.prototype.handleBalanceSuccess = function(data)
    {
        console.log(this.createLabelButtonToClick);
        if (this.createLabelButtonToClick !== undefined) {
            this.createLabelButtonToClick = undefined;
            $('#' + this.createLabelButtonToClick).click();
            return;
        }
        $('.shipping-ledger-balance-amount').text(data.balance);
        this.getAjaxRequester().getNotificationHandler().success('Balance topped up successfully.');
    };
    return Settings;
});