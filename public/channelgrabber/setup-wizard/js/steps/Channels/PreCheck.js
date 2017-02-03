define([
    'popup/confirm',
    'AjaxRequester'
], function(
    Confirm,
    ajaxRequester
)
{
    function PreCheck()
    {
        this.getAjaxRequester = function () {
            return ajaxRequester;
        };
    }

    PreCheck.prototype.getPreCheckActionFunction = function () {
        return {
            'amazon': function (region, callback) {
                var message = "Would you like to automatically import your FBA orders into OrderHub? <br/> <small>We can only send invoices to imported orders.</small>";
                var confirm = new Confirm(message, function(answer){
                    if(! answer) {
                        return;
                    }
                    var sessionData = {};
                    if (answer === 'Yes') {
                        sessionData['fbaOrderImport'] = 'on';
                    }
                    callback('amazon', region, { 'amazon': JSON.stringify(sessionData) });
                });
            }
        }
    };

    PreCheck.prototype.getPreCheckSaveFunction = function () {
        var self = this;
        return {
            'amazon': function (accountId) {
                var saveAccountUri = '/amazon/account/save';
                var sessionData = JSON.parse(sessionStorage.getItem('amazon'));
                sessionData['accountId'] = accountId;
                self.getAjaxRequester().sendRequest(saveAccountUri, sessionData, function () {}, function () {});
            }
        }
    };

    PreCheck.prototype.checkAndSaveData = function (channel, accountId) {
        var sessionData = sessionStorage.getItem(channel);
        if (!sessionData) {
            return;
        }
        var alreadySaved = sessionStorage.getItem(channel+accountId);
        if (alreadySaved) {
            return;
        }

        if (this.getPreCheckSaveFunction()[channel]) {
            this.getPreCheckSaveFunction()[channel](accountId);
        }

        sessionStorage.setItem(channel+accountId, true);
        sessionStorage.removeItem(channel);
    };

    PreCheck.prototype.performPreCheck = function (channel, region, callback) {
        if (! this.getPreCheckActionFunction()[channel]) {
            callback(channel, region);
            return;
        }
        this.getPreCheckActionFunction()[channel](region, callback);
    };

    return PreCheck;
});