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
        const minHeight = 205; // px

        const tableColumns = TableStorage.getDefaultColumns();
        const tableSortBy = TableStorage.getDefaultSortBy();
        const totals = TableStorage.getDefaultTableTotals();
        const tableCells = OrderTableHelper.formatDefaultTableCellsFromColumns(tableColumns);

        const sumOfColumnWidths = OrderTableHelper.getSumOfAllColumnWidths(tableColumns);
        const minWidthToSet = Number(sumOfColumnWidths).mmToPx();

        const additionalData = {
            errorBorder: false,
            showVat: false,
            linkedProductsDisplay: null,
            tableColumns,
            tableSortBy,
            tableCells,
            totals
        };

        ElementAbstract.call(this, additionalData);

        this.set('type', 'OrderTable', true);
        this.setWidth(elementWidth.pxToMm())
            .setHeight(minHeight.pxToMm())
            .setMinWidth(minWidthToSet)
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

        this.getTableTotals = function() {
            return this.get('totals');
        };

        this.setTableTotals = function(tableTotals) {
            this.set('totals', tableTotals);
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
            json.tableColumns = formatTableColumnsForBackend(json);
            json.tableSortBy = formatTableSortByForBackend(json.tableSortBy);
            json.totals = formatTableTotalsForBackend(json.totals);
            return json;
        };

        this.hydrate = function(data, populating) {
            this.setMinWidth(data.minWidth, populating);
            OrderTable.prototype.hydrate.call(this, data, populating);
        };
    };

    OrderTable.prototype = Object.create(ElementAbstract.prototype);

    return OrderTable;

    function formatTableTotalsForBackend(tableTotals) {
        if (!tableTotals) {
            return [];
        }
        return tableTotals.map(({id, displayText, position}) => {
            return {
                id,
                position,
                displayText
            };
        });
    }

    function formatTableColumnsForBackend({tableColumns, width}) {
        if (!tableColumns) {
            return [];
        }

        const columnIdsThatNeedWidths = tableColumns.filter((column) => (
            !column.width || !column.widthMeasurementUnit
        )).map((column) => column.id);
        const widthToSet = Number(width / columnIdsThatNeedWidths.length).pxToMm();
    
        const formatted = tableColumns.map(({id, position, displayText, width, widthMeasurementUnit}) => {
            let desiredWidth = width;
            let desiredWidthMeasurementUnit = widthMeasurementUnit;
            if (columnIdsThatNeedWidths.includes(id)){
                desiredWidth = widthToSet;
                desiredWidthMeasurementUnit = 'mm'
            }
            return {
                id,
                position,
                displayText,
                width: desiredWidth,
                widthMeasurementUnit: desiredWidthMeasurementUnit
            }
        });

        if (!areAllPositionsUndefined(formatted)) {
            return formatted;
        }

        return applyDefaultPositions(formatted.slice());
    }

    function formatTableSortByForBackend(tableSortBy) {
        if (!tableSortBy) {
            return [];
        }

        return tableSortBy.map((sortByItem) => {
            let {id, position} = sortByItem;
            return {
                column: id,
                position
            };
        });
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

    function applyDefaultPositions(columns) {
        for (let index = 0; index < columns.length; index++) {
            columns[index].position = index;
        }
        return columns;
    }
});