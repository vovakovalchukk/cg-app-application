define([], function() {
    "use strict";
    
    
    
    let stateFilters = function() {
        var self = {};
        
        return {
            getRowData : (products,rowIndex) => {
                return products[rowIndex];
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
                let row = products[rowIndex];
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