require(["Orders/invoice"], function(InvoiceBulkAction)
{
    var invoiceBulkAction = new InvoiceBulkAction(
        "<?= $this->translate('Preparing to generate invoices') ?>",
        "<?= $this->translate('Generating invoices') ?>",
        "<?= $this->translate('Finished generating invoices') ?>"
    );
    invoiceBulkAction.init("<?= $selector ?>");
});
<?php
if(isset($order)) {
    $this->placeholder($id . '-' . $action)
    ->append('data-orders="' . htmlentities(json_encode([$order->getId()]), ENT_QUOTES) . '"');
    }
?>