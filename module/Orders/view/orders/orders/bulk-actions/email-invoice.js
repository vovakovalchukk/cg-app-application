require.config({
    paths: {
        EmailInvoice: "<?= $this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'js/EmailInvoice' ?>"
    }
});
require(
    ["EmailInvoice"],
    function(EmailInvoice) {
        var emailInvoice = new EmailInvoice(n);
        $("#<?= $id ?>").bulkActions("set", "<?= $action ?>", emailInvoice.action);
    }
);