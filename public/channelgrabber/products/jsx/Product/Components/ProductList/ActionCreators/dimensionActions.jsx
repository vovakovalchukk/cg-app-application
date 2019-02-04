"use strict";

let dimensionActions = (function() {
    return {
        changeDimensionValue: (productId, detail, newValue) => {
            return async function(dispatch, getState) {
                let currentDetailsFromProductState = getState.customGetters.getDetailsFromProductState(productId);

                dispatch({
                    type: "DIMENSION_VALUE_CHANGE",
                    payload: {
                        productId,
                        detail,
                        newValue,
                        currentDetailsFromProductState
                    }
                });
            }
        },
        setIsEditing: (productId, detail, setToBoolean) => {
            return {
                type: "IS_EDITING_SET",
                payload: {
                    productId,
                    detail,
                    setToBoolean
                }
            }
        },
        saveDetail: (row, detail) => {
            return async function(dispatch, getState) {
                if (row === null) {
                    return;
                }
                let state = getState();
                let value = state.dimensions[detail].byProductId[row.id].valueEdited;
                n.notice('Updating ' + detail + ' value.');
                let response = await setDetail(row, detail, value);
                if (response.exception) {
                    return dispatch({
                        type: "PRODUCT_DETAILS_CHANGE_FAILURE",
                        payload: response
                    });
                }
                return dispatch({
                    type: "PRODUCT_DETAILS_CHANGE_SUCCESS",
                    payload: {
                        value,
                        detail,
                        row
                    }
                });
            }
        },
        cancelInput: (row, detail) => {
            if (row === null) {
                return;
            }
            return ({
                type: "DIMENSION_CANCEL_INPUT",
                payload: {
                    detail,
                    variation: row
                }
            });

        }
    }
})();

export default dimensionActions;

async function setDetail(variation, detail, value) {
    return $.ajax({
        url: '/products/details/update',
        type: 'POST',
        dataType: 'json',
        data: {
            id: variation.details.id,
            detail: detail,
            value: value,
            sku: variation.sku
        },
        success: function(response) {
            return response;
        },
        error: function(error) {
            return error;
        }
    });
}