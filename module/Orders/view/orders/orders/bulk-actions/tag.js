require.config({
    paths: {
        TagBulkAction: "<?= $this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'js/tag' ?>"
    }
});
require(
    ["TagBulkAction"],
    function(TagBulkAction) {
        var tagBulkAction = new TagBulkAction(n);
        $("#<?= $id ?>").bulkActions("set", "<?= $action ?>", tagBulkAction.action);
    }
);
<?php
if(isset($order)) {
    $this->placeholder($id . '-' . $action)
    ->append('data-orders="' . htmlentities(json_encode([$order->getId()]), ENT_QUOTES) . '"');
    }
?>