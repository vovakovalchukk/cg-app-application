define([], function() {
    "use strict";
    
    let stateFilters = function() {
        return {
            getProductIndex: (products, productId) => {
                return products.findIndex((product) => {
                    return product.id === productId;
                });
            },
            getProductById: (products, productId) => {
                return products.find((product) => {
                    return product.id === productId;
                });
            },
            getRowData: (products, rowIndex) => {
                return products.visibleRows[rowIndex];
            },
            getCellData: (products, columnKey, rowIndex) => {
                let row = products.visibleRows[rowIndex];
                let keyToCellDataMap = {
                    sku: row['sku'],
                    name: row['name'],
                };
                let cellData = keyToCellDataMap[columnKey];
                // todo - change this dummy data to be something more significant from TAC-165 onwards
                if(columnKey.indexOf('dummy')>-1){
                    cellData = `${columnKey} ${rowIndex}`;
                }
                return cellData;
            }
        };
    };
    
    return stateFilters()
});