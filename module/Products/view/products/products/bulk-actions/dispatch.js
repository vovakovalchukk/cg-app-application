require.config({
    paths: {
        DispatchBulkAction: "<?= $this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'js/dispatch' ?>"
    }
});
require(
    ["DispatchBulkAction"],
    function(DispatchBulkAction) {
        var dispatchBulkAction = new DispatchBulkAction(n);
        $("#<?= $id ?>").bulkActions("set", "<?= $action ?>", dispatchBulkAction.action);
    }
);
<?php
if(isset($order)) {
    $this->placeholder($id . '-' . $action)
        ->append('data-orders="' . htmlentities(json_encode([$order->getId()]), ENT_QUOTES) . '"');
}
?>