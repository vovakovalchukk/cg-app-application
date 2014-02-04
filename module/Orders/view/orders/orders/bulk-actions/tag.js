<?php $this->headScript()->appendFile($this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'js/tag.js'); ?>
$("#<?= $id ?>").bulkActions(
    "set",
    "<?= $action ?>",
    function(event) {
        var tagBulkAction = new TagBulkAction(
            "<?= $this->url('Orders/tag') ?>",
            <?= isset($tag) ? '"' . $tag . '"' : '$.trim(window.prompt("Name of Tag:", "tag"))' ?>,
            <?= isset($order) ? '["' . $order->getId() . '"]' : '$("#datatable").cgDataTable("selected", ".order-id")' ?>
        );
        return tagBulkAction.action.call(tagBulkAction, event);
    }
);