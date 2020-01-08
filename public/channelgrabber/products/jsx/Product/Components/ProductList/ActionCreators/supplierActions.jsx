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
                    n.error(error);
                }
            }
        },
        addNewSupplier: (productId, supplierName) => {
            return async function(dispatch) {
                try {
                    n.notice('Saving the new supplier...', true);
                    let response = await updateSupplier(productId, null, supplierName);
                    if (response.success === false) {
                        throw new Error('There was an error while saving the supplier. Please try again or contact support of the problem persists');
                    }

                    console.log(response);
                    dispatch({
                        type: "SAVE_SUPPLIER_SUCCESS",
                        payload: {
                            productId,
                            supplierName,
                            supplierId: response.supplierId,
                        }
                    });
                } catch (error) {
                    n.error(error);
                }
            }
        }
    };
})();

export default supplierActions;

async function updateSupplier(productId, supplierId, supplierName) {
    const data = {productId};
    supplierId ? data.supplierId = supplierId : false;
    supplierName ? data.supplierName = supplierName : false;
    return $.ajax({
        url: '/products/supplier',
        data: data,
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