define([], function()
{
    function Messages(notifications, saveEmailInvoicesUrl)
    {
        this.getNotifications = function()
        {
            return notifications;
        };

        this.getSaveEmailInvoicesUrl = function()
        {
            return saveEmailInvoicesUrl;
        };

        var init = function()
        {
            this.listenForEmailInvoicesToggle();
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

    return Messages;
});