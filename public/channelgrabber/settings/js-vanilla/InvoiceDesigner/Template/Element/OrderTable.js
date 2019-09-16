define([
    'InvoiceDesigner/Template/ElementAbstract',
    'InvoiceDesigner/Template/Storage/Table'
], function(
    ElementAbstract,
    TableStorage
) {
    var OrderTable = function() {
        const elementWidth = 700; // px
        const minHeight = 200; // px

        const tableColumns = TableStorage.getDefaultColumns();

        var additionalData = {
            showVat: false,
            linkedProductsDisplay: null,
            tableColumns
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

        this.toJson = function() {
            let json = JSON.parse(JSON.stringify(this.getData()));
            json = this.formatCoreJsonPropertiesForBackend(json);
            json.tableColumns = formatTableColumnsForBackend(json.tableColumns);
            return json;
        }
    };

    OrderTable.prototype = Object.create(ElementAbstract.prototype);

    return OrderTable;

    function formatTableColumnsForBackend(tableColumns) {
        if(!tableColumns) {
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
        if(!allPositionsUndefined) {
            return formatted;
        }

        const formattedWithDefaultPositions = provideDefaultPositions(formatted.slice());
        return formattedWithDefaultPositions;
    }

    function areAllPositionsUndefined(columns) {
        let allPositionsUndefined = true;
        for(let column of columns) {
            if(typeof column.position !== 'undefined'){
                allPositionsUndefined = false;
                break;
            }
        }
        return allPositionsUndefined;
    }
    
    function provideDefaultPositions(columns) {
        for(let index = 0; index < columns.length; index++) {
            columns[index].position = index;
        }
        return columns;
    }
});