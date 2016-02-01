require(["Orders/batch"], function(Batch)
{
    var batchBulkAction = new Batch('#batch');
    batchBulkAction.init('<?=$selector;?>');
});