define([
    'Product/Components/ProductList/Column/service'
], function(
    columnService
) {
    "use strict";
    
    let bulkSelectActions = (function() {
        return {
            changeProductBulkSelectStatus: (productId, checked) => {
                return function(dispatch) {
                    dispatch({
                        type: "BULK_SELECT_PRODUCT_STATUS_CHANGE",
                        payload: {
                            productId, checked
                        }
                    });
                }
            },
            deleteProducts: ()=>{
                return function(dispatch,getState) {
                    let state = getState();
                    
                    let selectedProducts = getState.customGetters.getSelectedProducts();
                    
                    console.log('selectedProducts: ', selectedProducts);
                    
                    
                    dispatch({
                        type:"PRODUCTS_DELETE",
                        payload:{}
                    });
                }
            }
        };
    })();
    
    return bulkSelectActions;
});