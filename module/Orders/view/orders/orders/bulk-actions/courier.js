require.config({
    paths: {
        CourierAction: "<?= $this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'js/courier' ?>"
    }
});
require(
    ["CourierAction"],
    function(CourierAction) {
        var courierAction = new CourierAction();
        $("#<?= $id ?>").bulkActions("set", "<?= $action ?>", function() {
            courierAction.action(this);
        });
    }
);
<?php
if(isset($order)) {
    $this->placeholder($id . '-' . $action)
    ->append('data-orders="' . htmlentities(json_encode([$order->getId()]), ENT_QUOTES) . '"');
}
?>