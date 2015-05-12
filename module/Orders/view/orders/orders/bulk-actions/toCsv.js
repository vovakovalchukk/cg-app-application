require.config({
    paths: {
        ToCsvBulkAction: "<?= $this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'js/toCsv' ?>",
        ProgressCheckAbstract: "<?= $this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'js/ProgressCheckAbstract' ?>"
    }
});
require(
    ["ToCsvBulkAction"],
    function(ToCsvBulkAction) {
        var toCsvBulkAction = new ToCsvBulkAction(
            n,
            "<?= $this->translate('Preparing to export CSV') ?>",
            "<?= $this->translate('Generating CSV') ?>",
            "<?= $this->translate('Finished generating CSV') ?>"
        );
        $("#<?= $id ?>").bulkActions("set", "<?= $action ?>", function() {
            toCsvBulkAction.setElement(this);
            toCsvBulkAction.action();
        });
    }
);
<?php
if(isset($order)) {
    $this->placeholder($id . '-' . $action)
    ->append('data-orders="' . htmlentities(json_encode([$order->getId()]), ENT_QUOTES) . '"');
}
?>