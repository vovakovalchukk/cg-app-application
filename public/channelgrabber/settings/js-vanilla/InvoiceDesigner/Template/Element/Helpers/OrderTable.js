define([], function() {
    const OrderTableHelper = function() {
        return this;
    };

    OrderTableHelper.prototype.formatTableCellsFromColumns = function(columns) {
        const tableCells = [];
        for (let column of columns) {
            let cellHeader = {
                column: column.id,
                cellTag: 'th',
                bold: true
            };
            let cellData = {
                column: column.id,
                cellTag: 'td',
                fontFamily: 'Courier'
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

    return new OrderTableHelper;
})