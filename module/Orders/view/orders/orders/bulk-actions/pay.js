require(["Orders/pay"], function(Pay) {
        var payBulkAction = new Pay(n);
        payBulkAction.init('<?=$selector;?>');
    }
);
<?php
if(isset($order)) {
    $this->placeholder($id . '-' . $action)
    ->append('data-orders="' . htmlentities(json_encode([$order->getId()]), ENT_QUOTES) . '"');
}
?>