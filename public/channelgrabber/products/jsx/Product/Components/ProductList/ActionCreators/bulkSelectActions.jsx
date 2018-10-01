define([
    'Product/Components/ProductList/Column/service'
], function(
    columnService
) {
    "use strict";
    
    let bulkSelectActions = (function() {
        return {
            changeProductBulkSelectStatus: (productId, checked) => {
                return function(dispatch, getState) {
                    
                    dispatch({
                        type: "BULK_SELECT_PRODUCT_STATUS_CHANGE",
                        payload: {
                            productId, checked
                        }
                    });
                }
            },
        };
    })();
    
    return bulkSelectActions;
});