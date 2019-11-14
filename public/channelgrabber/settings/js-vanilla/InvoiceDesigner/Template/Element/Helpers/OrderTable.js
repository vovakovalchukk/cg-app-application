const minColumnWidths = {
    'mm': 10
};
const tableCellIdPrefix = 'table-element-cell_';

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

    OrderTableHelper.prototype.getTableCellIdPrefix = function() {
        return tableCellIdPrefix;
    }

    OrderTableHelper.prototype.generateCellDomId = function(columnId, tag, elementId) {
        return `${this.getTableCellIdPrefix()}${columnId}-${tag}-${elementId}`;
    };

    OrderTableHelper.prototype.getColumnIndexForCell = function(tableColumns, cell) {
        return tableColumns.findIndex(column => {
            return column.id === cell.column
        });
    };

    OrderTableHelper.prototype.getSumOfAllColumnWidths = function(tableColumns) {
        let sumOfColumnWidths = 0;
        
        tableColumns.forEach((column) => {
            if (!column.width) {
                return;
            }
            sumOfColumnWidths += getColumnWidthInMm(column);
        });

        return sumOfColumnWidths;
    };

    OrderTableHelper.prototype.getColumnIdsThatNeedWidths = function(tableColumns) {
        return tableColumns.filter((column) => (
            !column.width || !column.widthMeasurementUnit
        )).map((column) => column.id);
    };

    return new OrderTableHelper;
});

function getColumnWidthInMm(column) {
    if (!column.width) {
        return minColumnWidths['mm'];
    }
    if (column.widthMeasurementUnit === 'in') {
        return column.width * 25.4;
    }
    return column.width;
}