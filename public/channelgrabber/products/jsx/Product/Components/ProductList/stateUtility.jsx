define([], function() {
    "use strict";
    
    let stateUtility = function() {
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
                    image: getImageData(row),
                    available: stateUtility().getStockAvailable(row)
                };
                let cellData = keyToCellDataMap[columnKey];
                if (columnKey.indexOf('dummy') > -1) {
                    cellData = `${columnKey} ${rowIndex}`;
                }
                return cellData;
            },
            isParentProduct: (rowData) => {
                return rowData.variationCount !== undefined && rowData.variationCount >= 1
            },
            getStockAvailable(rowData) {
                return stateUtility().getOnHandStock(rowData) - Math.max(stateUtility().getAllocatedStock(rowData), 0);
            },
            getOnHandStock: function(rowData) {
                return (rowData.stock ? rowData.stock.locations[0].onHand : '');
            },
            getAllocatedStock: function(rowData) {
                return (rowData.stock ? rowData.stock.locations[0].allocated : '');
            },
            getProductIdFromSku(products,sku){
                return products.find((product)=>{
                    return product.sku === sku;
                }).id
            },
            getPaginationLimit(state){
                return state.pagination.limit;
            },
            getCurrentPageNumber(state){
                return state.pagination.page;
            },
            getCurrentSearchTerm(state){
                return state.search.searchTerm;
            },
            getVisibleFixedColumns(state) {
                console.log('in getVisibleFixedColumns with state: ' , state);
                return state.columns.filter((column) => {
                    return column.fixed
                });
            }
        };
    };
    
    return stateUtility();
    
    function getImageData(row) {
        if (!row.images || !row.images.length) {
            return;
        }
        let primaryImage = row.images[0];
        return {
            id: primaryImage.id,
            url: primaryImage.url
        };
    }
});