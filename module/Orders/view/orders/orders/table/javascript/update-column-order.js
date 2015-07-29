$("#<?= $tableId ?>").on("fnPreDrawCallback", function(event, oSettings) {
    if (!oSettings.aoColumns || !oSettings.aoColumnsOrder) {
        return;
    }

    var columnData = {};
    for (var position in oSettings.aoColumnsOrder) {
        var column = oSettings.aoColumns[position];
        if (!column || !column.mData) {
            continue;
        }
        columnData['mDataProp_' + position] = column.mData;
    }

    $.ajax({
        url: "<?= $this->url($route) ?>",
        type: 'POST',
        data: columnData
    });
});
