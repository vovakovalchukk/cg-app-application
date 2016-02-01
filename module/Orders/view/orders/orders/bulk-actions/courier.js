require(["Orders/courier"], function(CourierAction)
{
    var courierAction = new CourierAction();
    courierAction.init('<?=$selector;?>');
});
<?php
if(isset($order)) {
    $this->placeholder($id . '-' . $action)
    ->append('data-orders="' . htmlentities(json_encode([$order->getId()]), ENT_QUOTES) . '"');
}
?>