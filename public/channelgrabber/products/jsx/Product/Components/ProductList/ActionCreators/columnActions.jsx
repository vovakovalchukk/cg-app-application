define([
    'Product/Components/ProductList/Column/service'
], function(
    columnService
) {
    "use strict";
    
    let columnActions = (function() {
        return {
            generateColumnSettings: (accounts) => {
                return {
                    type: "COLUMNS_GENERATE_SETTINGS",
                    payload: {
                        columnSettings: columnService.generateColumnSettings(accounts)
                    }
                };
            },
        };
    })();
    
    return columnActions;
});