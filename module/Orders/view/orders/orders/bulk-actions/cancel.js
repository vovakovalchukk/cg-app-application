<?php use CG\Order\Shared\Cancel\Value; ?>
require.config({
    paths: {
        cancel: "<?= $this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'js/cancel' ?>"
    }
});
require(
    ["cancel"],
    function(Cancel) {
        var reasons = <?= $cancellationReasons ?>;
        var cancelBulkAction = new Cancel(n, reasons, '<?= $this->translate(ucwords($type)) ?>');
        cancelBulkAction.init(<?= json_encode($this->templateMap) ?>);
        $("#<?= $id ?>").bulkActions("set", "<?= $action ?>", function() {
            cancelBulkAction.setSelector(this);
            cancelBulkAction.action();
        });
    }
);
<?php
$placeholder = $this->placeholder($id . '-' . $action);
$placeholder->append('data-popup="' . htmlentities(json_encode($templateMap), ENT_QUOTES) . '"');
if (isset($order)) {
    $placeholder->append('data-orders="' . htmlentities(json_encode([$order->getId()]), ENT_QUOTES) . '"');
}
?>