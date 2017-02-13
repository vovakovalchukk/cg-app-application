define([
    'popup/confirm',
    'AjaxRequester'
], function(
    Confirm,
    ajaxRequester
)
{
    function Prompter()
    {
        this.getAjaxRequester = function () {
            return ajaxRequester;
        };
    }

    Prompter.prototype.getPrompterActionFunction = function () {
        return {
            'amazon': function (accountId) {
                var self = this;
                var saveAccountUri = '/amazon/account/save';
                var message = "Would you like to automatically import your FBA orders into OrderHub? <br/> <small>We can only send invoices to imported orders.</small>";
                var confirm = new Confirm(message, function(answer){
                    if(! answer) {
                        return;
                    }
                    var saveData = {};
                    if (answer === 'Yes') {
                        saveData['fbaOrderImport'] = 'on';
                    }
                    saveData['accountId'] = accountId;
                    self.getAjaxRequester().sendRequest(saveAccountUri, saveData);
                });
            }
        }
    };


    Prompter.prototype.checkAndPromptUser = function (channel, accountId) {
        var alreadySaved = sessionStorage.getItem(channel+accountId);
        if (alreadySaved) {
            return;
        }
        if (this.getPrompterActionFunction()[channel]) {
            this.getPrompterActionFunction()[channel](accountId);
        }
        sessionStorage.setItem(channel+accountId, true);
    };

    return Prompter;
});