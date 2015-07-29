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
        data: columnData,
        success: function(data) {
            if (!data.updated) {
                var errorMsg = (data.error ? data.error : 'There was a problem saving column order (code: 1)');
                n.error(errorMsg);
                return;
            }
            n.success('Column order saved');
        },
        error: function (error, textStatus, errorThrown) {
            n.error('There was a problem saving column order (code: 2)');
            return;
        }
    });
});
