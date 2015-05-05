require.config({
    paths: {
        InvoiceBulkAction: "<?= $this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'js/invoice' ?>",
        ProgressCheckAbstract: "<?= $this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'js/ProgressCheckAbstract' ?>"
    }
});
require(
    ["InvoiceBulkAction"],
    function(InvoiceBulkAction) {
        var invoiceBulkAction = new InvoiceBulkAction(
            n,
            "<?= $this->translate('Preparing to generate invoices') ?>",
            "<?= $this->translate('Generating invoices') ?>",
            "<?= $this->translate('Finished generating invoices') ?>"
        );
        $("#<?= $id ?>").bulkActions("set", "<?= $action ?>", function() {
            invoiceBulkAction.setElement(this);
            invoiceBulkAction.action();
        });
    }
);
<?php
if(isset($order)) {
    $this->placeholder($id . '-' . $action)
    ->append('data-orders="' . htmlentities(json_encode([$order->getId()]), ENT_QUOTES) . '"');
    }
?>