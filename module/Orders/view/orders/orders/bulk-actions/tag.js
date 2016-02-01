require(["Orders/tag"], function(TagBulkAction)
{
    var tagBulkAction = new TagBulkAction(n, "<?= Orders\Module::PUBLIC_FOLDER ?>template/popup/saveTag.mustache");
    tagBulkAction.init('<?=$selector;?>');
});
<?php
if(isset($order)) {
    $this->placeholder($id . '-' . $action)
    ->append('data-orders="' . htmlentities(json_encode([$order->getId()]), ENT_QUOTES) . '"');
    }
?>