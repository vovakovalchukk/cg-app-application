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
            var data = this.getOriginalEmailAddressData();
            // Nothing to save, allow the normal Next action to continue
            if ($.isEmptyObject(data)) {
                return true;
            }

            return new Promise(function(resolve, reject)
            {
                self.saveOriginalEmailAddresses(data).then(function()
                {
                    resolve();
                }, function()
                {
                    reject();
                });
            });
        });
    };

    Messages.prototype.getOriginalEmailAddressData = function()
    {
        var data = {};
        $('.setup-wizard-messages-amazon-original-email').each(function()
        {
            if ($(this).val() == '') {
                return true; //continue
            }
            var accountId = $(this).data('account');
            data[accountId] = $(this).val();
        });
        return data;
    };

    Messages.prototype.saveOriginalEmailAddresses = function(data)
    {
        return $.ajax({
            url:
        });
    };

    return Messages;
});