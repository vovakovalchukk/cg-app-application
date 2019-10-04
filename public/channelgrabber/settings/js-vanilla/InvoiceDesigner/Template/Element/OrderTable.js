define([
    'InvoiceDesigner/Template/ElementAbstract',
    'InvoiceDesigner/Template/Storage/Table',
    'InvoiceDesigner/Template/Element/Helpers/OrderTable'
], function(
    ElementAbstract,
    TableStorage,
    OrderTableHelper
) {
    const OrderTable = function() {
        const elementWidth = 700; // px
        const minHeight = 200; // px

        const tableColumns = TableStorage.getDefaultColumns();
        const tableSortBy = TableStorage.getDefaultSortBy();

        const tableCells = OrderTableHelper.formatDefaultTableCellsFromColumns(tableColumns);

        const additionalData = {
            errorBorder: false,
            showVat: false,
            linkedProductsDisplay: null,
            tableColumns,
            tableSortBy,
            tableCells
        };

        ElementAbstract.call(this, additionalData);

        this.set('type', 'OrderTable', true);
        this.setWidth(elementWidth.pxToMm())
            .setHeight(minHeight.pxToMm())
            .setMinWidth(elementWidth)
            .setMaxWidth(elementWidth)
            .setMinHeight(minHeight);

        this.getLinkedProductsDisplay = function() {
            return this.get('linkedProductsDisplay');
        };

        this.setLinkedProductsDisplay = function(newLinkedProductsDisplay) {
            this.set('linkedProductsDisplay', newLinkedProductsDisplay);
            return this;
        };

        this.getShowVat = function() {
            return this.get('showVat');
        };

        this.setShowVat = function(newShowVat) {
            this.set('showVat', !!newShowVat);
            return this;
        };

        this.getTableColumns = function() {
            return this.get('tableColumns');
        };

        this.setTableColumns = function(tableColumns) {
            return this.set('tableColumns', tableColumns);
        };

        this.getTableSortBy = function() {
            return this.get('tableSortBy');
        };

        this.setTableSortBy = function(newSortBy) {
            this.set('tableSortBy', newSortBy);
        };

        this.getTableCells = function() {
            return this.get('tableCells').slice();
        };

        this.setTableCells = function(tableCells) {
            return this.set('tableCells', tableCells);
        };

        this.getActiveCellNodeId = function() {
            return this.get('activeCellNodeId');
        };

        this.setActiveCellNodeId = function(nodeId, populating) {
            let activeCellNodeId = this.getActiveCellNodeId();
            if(activeCellNodeId === nodeId){
                return;
            }
            return this.set('activeCellNodeId', nodeId, populating, true);
        };

        this.toJson = function() {
            let json = JSON.parse(JSON.stringify(this.getData()));
            json = this.formatCoreJsonPropertiesForBackend(json);
            json.tableColumns = formatTableColumnsForBackend(json.tableColumns);
            json.tableSortBy = formatTableSortByForBackend(json.tableSortBy);
            return json;
        }
    };

    OrderTable.prototype = Object.create(ElementAbstract.prototype);

    return OrderTable;

    function formatTableColumnsForBackend(tableColumns) {
        if (!tableColumns) {
            return [];
        }
        const formatted = tableColumns.map(({id, position, displayText}) => {
            return {
                id,
                position,
                displayText
            };
        });

        const allPositionsUndefined = areAllPositionsUndefined(formatted);
        if (!allPositionsUndefined) {
            return formatted;
        }

        const formattedWithDefaultPositions = provideDefaultPositions(formatted.slice());
        return formattedWithDefaultPositions;
    }

    function formatTableSortByForBackend(tableSortBy) {
        if (!tableSortBy) {
            return [];
        }

        const formatted = tableSortBy.map((sortByItem) => {
            let {id, position} = sortByItem;
            return {
                column: id,
                position
            };
        });

        return formatted;
    }

    function areAllPositionsUndefined(columns) {
        let allPositionsUndefined = true;
        for (let column of columns) {
            if (typeof column.position !== 'undefined') {
                allPositionsUndefined = false;
                break;
            }
        }
        return allPositionsUndefined;
    }

    function provideDefaultPositions(columns) {
        for (let index = 0; index < columns.length; index++) {
            columns[index].position = index;
        }
        return columns;
    }
});