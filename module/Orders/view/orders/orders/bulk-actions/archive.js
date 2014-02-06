require.config({
    paths: {
        ArchiveBulkAction: "<?= $this->baseUrl . Orders\Module::PUBLIC_FOLDER . 'js/archive' ?>"
    }
});
require(
    ["ArchiveBulkAction"],
    function(ArchiveBulkAction) {
        var archiveBulkAction = new ArchiveBulkAction(n);
        $("#<?= $id ?>").bulkActions("set", "<?= $action ?>", archiveBulkAction.action);
    }
);