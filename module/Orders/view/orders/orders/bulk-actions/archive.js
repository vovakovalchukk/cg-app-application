require(["Orders/archive"], function(ArchiveBulkAction)
{
    var archiveBulkAction = new ArchiveBulkAction();
    archiveBulkAction.init('<?=$selector;?>');
});