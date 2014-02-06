<?php $this->headScript()->appendFile($this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'js/tag.js'); ?>
<?php
if(isset($order)) {
    $this->placeholder($id . '-' . $action)
        ->append('data-orders="' . htmlentities(json_encode([$order->getId()]), ENT_QUOTES) . '"');
}
?>
$("#<?= $id ?>").bulkActions("set", "<?= $action ?>", TagBulkAction);