require(["Orders/pickList"], function(PickListBulkAction)
{
    var pickListBulkAction = new PickListBulkAction(
        "<?= $this->translate('Preparing to generate pick list') ?>",
        "<?= $this->translate('Generating pick list') ?>",
        "<?= $this->translate('Finished generating the pick list') ?>"
    );
    pickListBulkAction.init("<?= $selector ?>");
});
<?php
if(isset($order)) {
    $this->placeholder($id . '-' . $action)
    ->append('data-orders="' . htmlentities(json_encode([$order->getId()]), ENT_QUOTES) . '"');
}
?>