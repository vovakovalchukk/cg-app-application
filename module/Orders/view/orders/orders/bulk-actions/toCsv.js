require(["Orders/toCsv"], function(ToCsvBulkAction)
{
    var toCsvBulkAction = new ToCsvBulkAction(
        n,
        "<?= $this->translate('Preparing to export CSV') ?>",
        "<?= $this->translate('Generating CSV') ?>",
        "<?= $this->translate('Finished generating CSV') ?>"
    );
    toCsvBulkAction.init("<?= $selector ?>");
});
<?php
if(isset($order)) {
    $this->placeholder($id . '-' . $action)
    ->append('data-orders="' . htmlentities(json_encode([$order->getId()]), ENT_QUOTES) . '"');
}
?>