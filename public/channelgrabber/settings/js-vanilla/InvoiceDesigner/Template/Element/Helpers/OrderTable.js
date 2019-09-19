define([], function() {
    const OrderTableHelper = function() {
        return this;
    };

    OrderTableHelper.prototype.formatTableCellsFromColumns = function(columns) {
        console.log('in formatTableCellsFromColumns');
        const tableCells = [];
        for (let column of columns) {
            let cellHeader = {
                column: column.id,
                cellTag: 'th'
            };
            let cellData = {
                column: column.id,
                cellTag: 'td'
            };
            tableCells.push(cellHeader);
            tableCells.push(cellData);
        }
        return tableCells;
    };

    OrderTableHelper.prototype.getCellDataFromDomId = function(id, tableCells) {
        let [columnId, tagFromId, elementId] = id.split('-');

        return tableCells.find(({column, cellTag}) => {
            return tagFromId === cellTag && column === columnId;
        });
    };

    OrderTableHelper.prototype.generateCellDomId = function(columnId, tag, elementId) {
        return `${columnId}-${tag}-${elementId}`;
    };

    return new OrderTableHelper;
})