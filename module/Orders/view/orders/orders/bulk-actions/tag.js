<?php $this->headScript()->appendFile($this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'orders/js/tag.js'); ?>
$("#<?= $id ?>").bulkActions(
    "set",
    "<?= $action ?>",
    (
        new TagBulkAction(
            "<?= $this->url('Orders/tag') ?>",
            <?= isset($order) ? '["' . $order->getId() . '"]' : '$("#datatable").cgDataTable("selected", ".order-id")' ?>,
            <?= isset($tag) ? '"' . $tag . '"' : '$.trim(window.prompt("Name of Tag:", "tag"))' ?>
        )
    ).action
);