define([
    'Product/Components/ProductList/Column/service'
], function(
    columnService
) {
    "use strict";
    
    var columnActions = (function() {
        return {
            generateColumns: (accounts) => {
                return {
                    type: "COLUMNS_GENERATE",
                    payload: {
                        accounts
                    }
                };
            },
        };
    })();
    
    return columnActions;
});