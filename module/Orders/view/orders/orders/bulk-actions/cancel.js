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
        var cancelBulkAction = new Cancel(n, reasons, '<?= ($order->getPaymentDate()) ? $this->translate(ucwords(Value::REFUND_TYPE)) :
        $this->translate(ucwords(Value::CANCEL_TYPE)); ?>');
        $("#<?= $id ?>").bulkActions("set", "<?= $action ?>", function() {
            cancelBulkAction.setSelector(this);
            cancelBulkAction.action();
        });
    }
);
<?php if ($order->getPaymentDate()): ?>
$(document).ready(function(){
    $('.icon-med.archive').addClass('accounting').removeClass('archive').parent().children('.title').html('<?= $this->translate('Refund'); ?>');
});
<?php endif; ?>

<?php
if (isset($order)) {
    $this->placeholder($id . '-' . $action)
    ->append('data-orders="' . htmlentities(json_encode([$order->getId()]), ENT_QUOTES) . '"')
    ->append('data-popup="/channelgrabber/orders/template/popups/cancelOptions.html"');
}
?>