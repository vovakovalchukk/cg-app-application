require.config({
    paths: {
        batch: "<?= $this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'js/batch' ?>"
    }
});
require(
    ["batch"],
    function(Batch) {
        var batchBulkAction = new Batch(n);
        $("#<?= $id ?>").bulkActions("set", "<?= $action ?>", batchBulkAction.action);
    }
);