const orderId = "<?php echo $order->getId(); ?>";

$('#product-bulk-action-duplicate').on('click', function() {
    window.location.href = '/orders/new/' + orderId;
});
