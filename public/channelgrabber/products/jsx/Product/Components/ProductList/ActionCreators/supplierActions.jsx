"use strict";

const supplierActions = (function() {
    return {
        storeOptions: (options) => {
            return function(dispatch) {
                dispatch({
                    type: "STORE_SUPPLIERS_OPTIONS",
                    payload: {
                        options
                    }
                });
            }
        },
        extractSuppliers: (products) => {
            return function(dispatch) {
                dispatch({
                    type: "EXTRACT_SUPPLIERS",
                    payload: {
                        products
                    }
                });
            }
        },
        updateSupplier: (productId, supplierId) => {
            return async function(dispatch) {
                try {
                    n.notice('Updating supplier...', true);
                    let response = await updateSupplier(productId, supplierId);
                    if (response.success === false) {
                        throw new Error('There was an error while updating the supplier. Please try again or contact support of the problem persists');
                    }

                    dispatch({
                        type: "UPDATE_SUPPLIER_SUCCESS",
                        payload: {
                            productId,
                            supplierId
                        }
                    });
                } catch (error) {
                    dispatch({
                        type: "UPDATE_SUPPLIER_FAILED",
                        payload: {
                            error
                        }
                    })
                }
            }
        }
    };
})();

export default supplierActions;

async function updateSupplier(productId, supplierId) {
    return $.ajax({
        url: '/products/supplier',
        data: {
            productId,
            supplierId
        },
        method: 'POST',
        dataType: 'json',
        success: function(response) {
            return response;
        },
        error: function(error) {
            return error;
        }
    });
}