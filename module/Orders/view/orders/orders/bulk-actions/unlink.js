require(["Orders/unlink"], function(Unlink) {
        var unlinkBulkAction = new Unlink(n);
        unlinkBulkAction.init('<?=$selector;?>');
    }
);
<?php
if (isset($order)) {
    $this->placeholder($id . '-' . $action)
    ->append('data-orders="' . htmlentities(json_encode([$order->getId()]), ENT_QUOTES) . '"');
}