define([
    'InvoiceDesigner/Template/ElementAbstract',
    'InvoiceDesigner/Template/Storage/Table',
    'InvoiceDesigner/Template/Element/Helpers/OrderTable',
    'InvoiceDesigner/utility',
    'Common/Common/Utils/generic'
], function(
    ElementAbstract,
    TableStorage,
    OrderTableHelper,
    invoiceDesignerUtility,
    genericUtils
) {
    const OrderTable = function() {
        const elementWidth = 700; // px
        const minHeight = 140; // px

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

        this.formatTableColumnsForBackend = function({tableColumns, width}) {
            if (!tableColumns) {
                return [];
            }

            const formatted = [...tableColumns].map(({id, position, displayText, width, widthMeasurementUnit}) => {
                return {
                    id,
                    position,
                    displayText,
                    width,
                    widthMeasurementUnit
                }
            });

            return formatted;
        };

        this.applyMissingDataForSave = function() {
            let tableColumns = [...this.getTableColumns()];
            let columns = applyMissingTableColumnWidths(tableColumns, this.getWidth());
            columns = applyDefaultTableColumnPositions(columns);
            this.setTableColumns(columns);
        };

        this.toJson = function() {
            let json = JSON.parse(JSON.stringify(this.getData()));

            json.tableColumns = this.formatTableColumnsForBackend(json);
            json = this.formatCoreJsonPropertiesForBackend(json);
            json.tableSortBy = formatTableSortByForBackend(json.tableSortBy);
            json.totals = formatTableTotalsForBackend(json.totals);
            return json;
        };

        this.hydrate = function(data, populating) {
            this.setMinWidth(data.minWidth, populating);
            OrderTable.prototype.hydrate.call(this, data, populating);
        };
    };    OrderTable.prototype.createElement = function() {
        return new TableElement();
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

    function applyMissingTableColumnWidths(tableColumns, elementWidth) {
        const hasWidthMeasurementUnit = column => column.widthMeasurementUnit;
        const hasWidth = column => !isNaN(column.width);

        const validWidthFilters = genericUtils.composeFilters(
            hasWidthMeasurementUnit,
            hasWidth
        );

        const validWidthColumns = tableColumns.filter(validWidthFilters);

        const sumOfExistingWidths = validWidthColumns.reduce((totalWidth, currentColumn) => {
            let currentWidth = currentColumn.widthMeasurementUnit === 'in' ?
                genericUtils.inToMm(currentColumn.width) : currentColumn.width;
            return totalWidth + currentWidth;
        }, 0);
        const widthToSetOnInvalidColumns = (elementWidth - sumOfExistingWidths) / (tableColumns.length - validWidthColumns.length);

        tableColumns.forEach(column => {
            if (validWidthColumns.includes(column)) {
                return;
            }
            column.width = widthToSetOnInvalidColumns;
            column.widthMeasurementUnit = 'mm'
        });

        return tableColumns;
    }

    function applyDefaultTableColumnPositions(columns) {
        for (let index = 0; index < columns.length; index++) {
            columns[index].position = index;
        }
        return columns;
    }
});