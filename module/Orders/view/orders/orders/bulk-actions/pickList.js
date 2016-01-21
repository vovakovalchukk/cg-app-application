require.config({
    paths: {
        PickListBulkAction: "<?= $this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'js/pickList' ?>"
    }
});
require(
    ["PickListBulkAction"],
    function(PickListBulkAction) {
        var pickListBulkAction = new PickListBulkAction(
            n,
            "<?= $this->translate('Preparing to generate pick list') ?>",
            "<?= $this->translate('Generating pick list') ?>",
            "<?= $this->translate('Finished generating the pick list') ?>"
        );
        $("#<?= $id ?>").bulkActions("set", "<?= $action ?>", function() {
            pickListBulkAction.setElement(this);
            pickListBulkAction.action();
        });
    }
);
<?php
if(isset($order)) {
    $this->placeholder($id . '-' . $action)
    ->append('data-orders="' . htmlentities(json_encode([$order->getId()]), ENT_QUOTES) . '"');
}
?>