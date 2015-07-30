$("#<?= $tableId ?>").on("fnPreDrawCallback", function(event, oSettings) {
    if (!oSettings.aoColumns || !oSettings.aoColumnsOrder) {
        return;
    }

    var columnOrder = {};
    $("th", oSettings.nTHead).each(function(position, column) {
        columnOrder[position] = $(column).data("columnIndex");
    });

    var match = true;
    var columnData = {};
    for (var position in columnOrder) {
        var column = oSettings.aoColumns[position];
        if (!column || !column.mData) {
            continue;
        } else if (columnOrder[position] != oSettings.aoColumnsOrder[position]) {
            match = false;
        }
        columnData['mDataProp_' + position] = column.mData;
    }

    if (match) {
        return;
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
