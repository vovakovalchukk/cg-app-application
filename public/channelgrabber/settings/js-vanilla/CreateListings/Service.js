define([
    'ajaxForm',
    'element/ElementCollection'
], function(
    AjaxForm,
    elementCollection
) {
    function Service(notifications)
    {
        this.getNotifications = function()
        {
            return notifications;
        };

        this.getElementCollection = function()
        {
            return elementCollection;
        };

        var init = function()
        {
            var form = new AjaxForm(notifications, Service.SELECTOR_FORM);
            this.listenForImportAndVerify();
            this.listenForSuccessAndResetForm();
        };
        init.call(this);
    }

    Service.SELECTOR_FORM = '#listing-import-form';
    Service.SELECTOR_ACCOUNT = '#listing-import-account-select';
    Service.SELECTOR_FILE = '#listing-import-file-upload-hidden-input';
    Service.SELECTOR_BUTTON = '#listing-import-button';

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

    Service.prototype.listenForSuccessAndResetForm = function()
    {
        var self = this;
        $(document).on('ajaxFormSubmitSuccess', function()
        {
            var select = self.getElementCollection().get('accountId');
            var fileUpload = self.getElementCollection().get('listingFile');
            select.clear();
            fileUpload.clear();
        });
    };

    return Service;
});