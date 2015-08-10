(function() {
    var columnOrder;

    $("#<?= $tableId ?>").on("fnInitComplete", function(event, oSettings) {
        if (columnOrder || !oSettings.nTHead) {
            return;
        }

        columnOrder = {};
        $("th", oSettings.nTHead).each(function(position, column) {
            columnOrder[position] = $(column).data("columnIndex");
        });
    });

    $("#<?= $tableId ?>").on("fnPreDrawCallback", function(event, oSettings) {
        if (!columnOrder || !oSettings.nTHead || !oSettings.aoColumns) {
            return;
        }

        var currentOrder = {};
        $("th", oSettings.nTHead).each(function(position, column) {
            currentOrder[position] = $(column).data("columnIndex");
        });

        var match = true;
        var columnData = {};
        for (var position in currentOrder) {
            var column = oSettings.aoColumns[position];
            if (!column || !column.mData) {
                continue;
            } else if (currentOrder[position] != columnOrder[position]) {
                match = false;
            }
            columnData['mDataProp_' + position] = column.mData;
        }

        if (match) {
            return;
        }

        columnOrder = currentOrder;

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
})();
