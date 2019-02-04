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
            console.log('in setIsEditing AQ');
            return {
                type: "IS_EDITING_SET",
                payload: {
                    productId,
                    detail,
                    setToBoolean
                }
            }
        },
        saveDetail: (variation, detail) => {
            return async function(dispatch, getState) {
                if (variation === null) {
                    return;
                }
                let value = getState().dimensions[detail].byProductId[variation.id].valueEdited;
                n.notice('Updating ' + detail + ' value.');
                let response = await setDetail(variation, detail, value);
                if (response.exception) {
                    return dispatch({
                        type: "PRODUCT_DETAILS_CHANGE_FAILURE",
                        payload: response
                    });
                }
                return dispatch({
                    type: "PRODUCT_DETAILS_CHANGE",
                    payload: {
                        value: value,
                        detail: detail,
                        row: variation
                    }
                });
            }
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