define([
    'ajaxForm'
], function(
    AjaxForm
) {
    function Service(notifications)
    {
        this.getNotifications = function()
        {
            return notifications;
        };

        var init = function()
        {
            var form = new AjaxForm(notifications, Service.SELECTOR_FORM);
            this.listenForImportAndVerify();
        };
        init.call(this);
    }

    Service.SELECTOR_FORM = '#product-import-form';
    Service.SELECTOR_ACCOUNT = '#product-import-account-select';
    Service.SELECTOR_FILE = '#product-import-file-upload-hidden-input';
    Service.SELECTOR_BUTTON = '#product-import-button';

    Service.prototype.listenForImportAndVerify = function()
    {
        var self = this;
        $(Service.SELECTOR_BUTTON).click(function(e)
        {
            var accountId = $(Service.SELECTOR_ACCOUNT).find('input[type=hidden]').val();
            if (!accountId || parseInt(accountId) == NaN || parseInt(accountId) < 1) {
                self.getNotifications().error('Please select an account to import to');
                e.preventDefault();
                return;
            }
            var file = $(Service.SELECTOR_FILE).val();
            if (!file) {
                self.getNotifications().error('Please select a file to import');
                e.preventDefault();
                return;
            }
        });
    };

    return Service;
});