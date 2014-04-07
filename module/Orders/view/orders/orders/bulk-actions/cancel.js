require.config({
    paths: {
        cancel: "<?= $this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'js/cancel' ?>"
    }
});
require(
    ["cancel"],
    function(Cancel) {
        var reasons = <?= $cancellationReasons ?>;
        var cancelBulkAction = new Cancel(n, reasons);
        $("#<?= $id ?>").bulkActions("set", "<?= $action ?>", cancelBulkAction.action);
    }
);
<?php
if(isset($order)) {
    $this->placeholder($id . '-' . $action)
    ->append('data-orders="' . htmlentities(json_encode([$order->getId()]), ENT_QUOTES) . '"');
    }
?>