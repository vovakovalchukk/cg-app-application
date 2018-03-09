define([
    'popup/confirm'
], function(
    Confirm
)
{
    function Prompter()
    {
        this.getNotificationHandler = function()
        {
            return n;
        };
    }

    Prompter.prototype.getPrompterActionFunction = function () {
        var self = this;
        return {
            'amazon': function (accountId) {
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
                    self.getNotificationHandler().notice('Saving your FBA settings');
                    $.ajax({
                        url : saveAccountUri,
                        data : saveData,
                        method : 'POST',
                        success: function (data) {
                            self.getNotificationHandler().success('Your FBA settings were saved successfully');
                            sessionStorage.setItem('amazon'+accountId, true);
                        },
                        error: function () {}
                    });
                });
            }
        }
    };


    Prompter.prototype.checkAndPromptUser = function (channel, accountId) {
        if (typeof channel !== 'string' || typeof accountId !== 'number') {
            return;
        }
        var alreadySaved = sessionStorage.getItem(channel+accountId);
        if (alreadySaved) {
            return;
        }
        if (this.getPrompterActionFunction()[channel]) {
            this.getPrompterActionFunction()[channel](accountId);
        }
    };

    return Prompter;
});