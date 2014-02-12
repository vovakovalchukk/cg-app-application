require.config({
    paths: {
        batch: "<?= $this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'js/batch' ?>"
    }
});
require(
    ["batch"],
    function(Batch) {
        var batchBulkAction = new Batch(n, '#batch', CGMustache);
        $("#<?= $id ?>").bulkActions("set", "<?= $action ?>", function() {
            return batchBulkAction.remove(this)
        });
    }
);