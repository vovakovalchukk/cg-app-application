define(['../SetupWizard.js'], function(setupWizard)
{
    function Messages(notifications, saveEmailInvoicesUrl, saveAmazonOriginalEmailUrl)
    {
        this.getNotifications = function()
        {
            return notifications;
        };

        this.getSaveEmailInvoicesUrl = function()
        {
            return saveEmailInvoicesUrl;
        };

        this.getSaveAmazonOriginalEmailUrl = function()
        {
            return saveAmazonOriginalEmailUrl;
        };

        this.getSetupWizard = function()
        {
            return setupWizard;
        };

        var init = function()
        {
            this.listenForEmailInvoicesToggle()
                .listenForAmazonMessagingSetupButtonClicks()
                .registerNextSave();
        };
        init.call(this);
    }

    Messages.prototype.listenForEmailInvoicesToggle = function()
    {
        var self = this;
        $('#email-invoice-dispatch-section form').on('change', 'input.toggle', function()
        {
            self.saveEmailInvoicesToggle(this);
        });
        return this;
    };

    Messages.prototype.listenForAmazonMessagingSetupButtonClicks = function()
    {
        $('.setup-wizard-messaging-add-button').click(function()
        {
            window.location = $(this).find('.action').data('action');
        });

        return this;
    };

    Messages.prototype.saveEmailInvoicesToggle = function(input)
    {
        var self = this;
        $(input).prop("disabled", true);
        $.ajax({
            type: "POST",
            url: self.getSaveEmailInvoicesUrl(),
            data: {
                autoEmail: $(input).is(':checked'),
                eTag: $('#email-invoice-dispatch-section form input[name=eTag]').val()
            }
        }).then(function(response)
        {
            $('#email-invoice-dispatch-section form input[name=eTag]').val(response.eTag);
        }, function()
        {
            self.getNotifications().error('There was a problem saving your settings');
            $(input).prop('checked', !$(input).prop('checked'));
        }).always(function()
        {
            $(input).prop("disabled", false);
        });
    };

    Messages.prototype.registerNextSave = function()
    {
        var self = this;
        this.getSetupWizard().registerNextCallback(function()
        {
            var data = self.getOriginalEmailAddressData();
            // Nothing to save, allow the normal Next action to continue
            if ($.isEmptyObject(data)) {
                return true;
            }

            self.getNotifications().notice('Saving settings');
            return new Promise(function(resolve, reject)
            {
                var promises = self.saveOriginalEmailAddresses(data);
                Promise.all(promises).then(function()
                {
                    self.getNotifications().success('Settings saved');
                    resolve();
                }, function()
                {
                    self.getNotifications().error('There was a problem saving your settings. Please try again.');
                    reject();
                });
            });
        });
    };

    Messages.prototype.getOriginalEmailAddressData = function()
    {
        var self = this;
        var data = {};
        $('.setup-wizard-messages-amazon-original-email').each(function()
        {
            if ($(this).val() == '') {
                return true; //continue
            }
            if (!$(this).val().match(/^.+?@.+?\..+$/)) {
                self.getNotifications().error('Please enter a valid email address');
                data = {};
                return false;
            }
            var accountId = $(this).data('account');
            data[accountId] = $(this).val();
        });
        return data;
    };

    Messages.prototype.saveOriginalEmailAddresses = function(data)
    {
        var promises = [];
        for (var accountId in data) {
            var accountPromise = $.ajax({
                url: this.getSaveAmazonOriginalEmailUrl(),
                data: {accountId: accountId, originalEmailAddress: data[accountId]},
                type: "POST"
            });
            promises.push(accountPromise);
        }
        return promises;
    };

    return Messages;
});