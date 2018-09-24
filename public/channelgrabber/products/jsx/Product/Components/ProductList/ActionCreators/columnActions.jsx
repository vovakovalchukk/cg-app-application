define([
    'Product/Components/ProductList/Column/service'
], function(
    columnService
) {
    "use strict";
    
    let columnActions = (function() {
        return {
            generateColumnSettings: () => {
                return function(dispatch, getState) {
                    let columnSettings = columnService.generateColumnSettings(
                        getState.customGetters.getAccounts().accounts,
                        getState.customGetters.getVat()
                    );
                    dispatch({
                        type: "COLUMNS_GENERATE_SETTINGS",
                        payload: {
                            columnSettings
                        }
                    });
                }
            },
        };
    })();
    
    return columnActions;
});