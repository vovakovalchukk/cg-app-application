"use strict";
// todo make this es6
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
        deleteProducts: () => {
            return async function(dispatch, getState) {
                let selectedProducts = getState.customGetters.getSelectedProducts();
                try {
                    let data = await deleteProducts(selectedProducts);
                    dispatch({
                        type: "PRODUCTS_DELETE_SUCCESS",
                        payload: {}
                    });
                    return data;
                } catch (err) {
                    console.error(err);
                    dispatch({
                        type: "PRODUCTS_DELETE_ERROR",
                        payload: {}
                    });
                }
            }
        }
    };
})();

export default bulkSelectActions;

async function deleteProducts(productsIds) {
    return $.ajax({
        'url': '/products/delete',
        'data': {
            'productIds': productsIds
        },
        'method': 'POST',
        'dataType': 'json',
        'success': function(data) {
            return data;
        },
        'error': function(err) {
            return err;
        }
    });
}