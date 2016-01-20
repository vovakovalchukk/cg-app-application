require.config({
    paths: {
        Orders: "<?= $this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'js/' ?>",
    }
});
require(
    ["Orders/invoice"],
    function(InvoiceBulkAction) {
        var invoiceBulkAction = new InvoiceBulkAction(
            n,
            "<?= $this->translate('Preparing to generate invoices') ?>",
            "<?= $this->translate('Generating invoices') ?>",
            "<?= $this->translate('Finished generating invoices') ?>"
        );
        $("#<?= $id ?>").bulkActions("set", "<?= $action ?>", function() {
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