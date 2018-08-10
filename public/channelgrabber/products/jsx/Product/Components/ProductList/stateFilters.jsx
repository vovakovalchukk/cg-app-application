define([], function() {
    "use strict";
    
    
    
    let stateFilters = function() {
        var self = {};
        
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
            getRowData : (products,rowIndex) => {
                return products.visibleRows[rowIndex];
            },
            //
            // addRow:(rowIndex)=>{
            //
            //
            //   self.data.splice(rowIndex, 0, {name:'dummy row'})
            //     console.log('in add Row... ' , self.data)
            //
            //
            // },
            getCellData: (products,columnKey, rowIndex) => {
                // return self.data[rowIndex][columnKey];
                let row = products.visibleRows[rowIndex];
                var keyToCellDataMap = {
                    sku : row['sku'],
                    name: row['name'],
                }
                let cellData = keyToCellDataMap[columnKey]
                return cellData;
            }
        };
    };
    
    return stateFilters()
    
 
});