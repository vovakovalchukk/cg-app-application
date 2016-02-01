require(["Orders/dispatch"], function(DispatchBulkAction)
{
    var dispatchBulkAction = new DispatchBulkAction();
    dispatchBulkAction.init('<?=$selector;?>');
});
<?php
if(isset($order)) {
    $this->placeholder($id . '-' . $action)
        ->append('data-orders="' . htmlentities(json_encode([$order->getId()]), ENT_QUOTES) . '"');
}
?>