require.config({
    paths: {
        InvoiceBulkAction: "<?= $this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'js/invoice' ?>"
    }
});
require(
    ["InvoiceBulkAction"],
    function(InvoiceBulkAction) {
        var invoiceBulkAction = new InvoiceBulkAction(n, "<?= $this->translate('Generating Invoice...') ?>");
        $("#<?= $id ?>").bulkActions("set", "<?= $action ?>", function() {
            invoiceBulkAction.setElement(this);
            invoiceBulkAction.action();
        });
    }
);