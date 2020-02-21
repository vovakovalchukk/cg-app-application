define([
    'Common/Common/Utils/generic'
], function(
    genericUtils
) {
    const minColumnWidths = {
        'mm': 15
    };
    const tableCellIdPrefix = 'table-element-cell_';

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
        let [columnId, tagFromId] = id.slice(this.getTableCellIdPrefix().length).split('-');

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
            sumOfColumnWidths += getColumnWidthInMm(column);
        });
        
        return sumOfColumnWidths;
    };

    return new OrderTableHelper;

    function getColumnWidthInMm(column) {
        if (!column.width || !column.widthMeasurementUnit) {
            return minColumnWidths['mm'];
        }
        if (column.widthMeasurementUnit === 'in') {
            return genericUtils.inToMm(column.width);
        }
        return column.width;
    }

});