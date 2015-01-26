require.config({
    paths: {
        PickListBulkAction: "<?= $this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'js/pickList' ?>",
        ProgressCheckAbstract: "<?= $this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'js/ProgressCheckAbstract' ?>"
    }
});
require(
    ["PickListBulkAction"],
    function(PickListBulkAction) {
        var pickListBulkAction = new PickListBulkAction(n, "<?= $this->translate('Generating pick list...') ?>");
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