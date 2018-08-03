define([], function() {
    "use strict";
    
    let tableDataWrapper = function() {
        var self = {};
        
        return {
            storeData: function(data) {
                self.data = data;
            },
            getCellData: (columnKey, rowIndex) => {
                // console.log('in getCellData with rowIndex,', rowIndex, 'and columnKey', columnKey);
                // console.log('self.data: ', self.data);
                
                if (!self.data) {
                    return;
                }
                return self.data[rowIndex][columnKey];
                
                
            },
            getData: () => {
                // console.log('in getData: ' , data);
                return self.data;
            }
        };
    };
    
    return tableDataWrapper()
});