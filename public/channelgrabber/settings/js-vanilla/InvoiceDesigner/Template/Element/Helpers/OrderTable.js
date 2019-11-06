define([], function() {
    const OrderTableHelper = function() {
        return this;
    };

    OrderTableHelper.prototype.formatDefaultTableCellsFromColumns = function(columns) {
        const tableCells = [];
        const commonDefaults = {
            backgroundColour: '',
            fontColour: '#222',
            align: 'left',
            fontFamily: 'Arial'
        };

        for (let column of columns) {
            let cellHeader = {
                column: column.id,
                cellTag: 'th',
                bold: true,
                fontSize: 10,
                ...commonDefaults
            };
            let cellData = {
                column: column.id,
                cellTag: 'td',
                fontSize: 9,
                ...commonDefaults
            };
            tableCells.push(cellHeader);
            tableCells.push(cellData);
        }
        return tableCells;
    };

    OrderTableHelper.prototype.getCellDataIndexFromDomId = function(id, tableCells) {
        let [columnId, tagFromId, elementId] = id.split('-');

        return tableCells.findIndex(({column, cellTag}) => {
            return tagFromId === cellTag && column === columnId;
        });
    };

    OrderTableHelper.prototype.generateCellDomId = function(columnId, tag, elementId) {
        return `${columnId}-${tag}-${elementId}`;
    };

    OrderTableHelper.prototype.getColumnIndexForCell = function(tableColumns, cell) {
        return tableColumns.findIndex(column => {
            return column.id === cell.column
        });
    };

    OrderTableHelper.prototype.getSumOfAllColumnWidths = function(tableColumns) {
        const safeMinWidthForColumn = 10;
        let numberOfColumnsWithWidths = 0;
        let sumOfColumnWidths = 0;
        
        tableColumns.forEach((column) => {
            if (!column.width) {
                return;
            }
            numberOfColumnsWithWidths ++;
            sumOfColumnWidths += column.width;
        });

        const numberOfUndefinedWidthColumns = tableColumns.length - numberOfColumnsWithWidths;
        sumOfColumnWidths = sumOfColumnWidths + (numberOfUndefinedWidthColumns * safeMinWidthForColumn);

        return sumOfColumnWidths;
    };

    return new OrderTableHelper;
});