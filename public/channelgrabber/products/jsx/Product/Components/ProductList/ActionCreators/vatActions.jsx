"use strict";

let vatActions = (function() {
    return {
        extractVatFromProducts: (products) => {
            return function(dispatch) {
                dispatch({
                    type: "VAT_FROM_PRODUCTS_EXTRACT",
                    payload: {
                        products
                    }
                });
            }
        },
        storeVatRates: (vatRates) => {
            return function(dispatch) {
                dispatch({
                    type: "VAT_RATES_STORE",
                    payload: {
                        vatRates
                    }
                });
            }
        },
        updateVat: (rowId, countryCode, desiredVal) => {
            return async function(dispatch) {
                try {
                    n.notice('Updating product tax rate.');
                    let response = await updateTaxRate(rowId, desiredVal);
                    dispatch({
                        type: "VAT_UPDATE_SUCCESS",
                        payload: {
                            rowId,
                            countryCode,
                            desiredVal,
                            response
                        }
                    });
                } catch (error) {
                    dispatch({
                        type: "VAT_UPDATE_FAILURE",
                        payload: {
                            error
                        }
                    })
                }
            }
        },
        toggleVatSelect: (productId, countryCode) => {
            return {
                type: 'VAT_SELECT_TOGGLE',
                payload: {
                    productId,
                    countryCode
                }
            }
        }
    }
})();

export default vatActions;

async function updateTaxRate(rowId, taxRateId) {
    return $.ajax({
        url: '/products/taxRate',
        data: {productId: rowId, taxRateId: taxRateId, memberState: taxRateId.substring(0, 2)},
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