require.config({
    paths: {
        TagBulkAction: "<?= $this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'js/tag' ?>"
    }
});
require(
    ["TagBulkAction"],
    function(TagBulkAction) {
        $("#<?= $id ?>").bulkActions("set", "<?= $action ?>", TagBulkAction);
    }
);
<?php
if(isset($order)) {
    $this->placeholder($id . '-' . $action)
    ->append('data-orders="' . htmlentities(json_encode([$order->getId()]), ENT_QUOTES) . '"');
    }
?>