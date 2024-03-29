import paginationActions from "Product/Components/ProductList/ActionCreators/paginationActions";

"use strict";

let bulkSelectActions = (function() {
    return {
        toggleSelectAllBulkSelect: () => {
            return function(dispatch, getState) {
                let allVisibleProductIds = getState().products.visibleRows.map(product => product.id);
                dispatch({
                    type: "SELECT_ALL_BULK_SELECT_TOGGLE",
                    payload: {
                        allVisibleProductIds
                    }
                });
            }
        },
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
                    n.notice('Deleting products.');
                    let data = await deleteProducts(selectedProducts);

                    dispatch({
                        type: "PRODUCTS_DELETE_SUCCESS",
                        payload: {deletedProducts: selectedProducts}
                    });

                    let state = getState();
                    let desiredPageNumber = getDesiredPageNumber(state, selectedProducts);

                    dispatch(paginationActions.changePage(desiredPageNumber));

                    return data;
                } catch (err) {
                    console.error(err);
                    n.error('There was an error deleting products.');
                    dispatch({
                        type: "PRODUCTS_DELETE_ERROR",
                        payload: {}
                    });
                }
            }
        }
    };

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
})();

export default bulkSelectActions;

function getDesiredPageNumber(state, selectedProducts) {
    let total = state.pagination.total;
    let limit = state.pagination.limit;
    return Math.ceil((total - selectedProducts.length) / limit);
}