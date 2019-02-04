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
            return async function(dispatch) {
                //todo - get value from state
                let value = getState().dimensions[detail].byProductId[variation.id].valueEdited;
                console.log('value to save...: ', value);

                if (variation === null) {
                    return;
                }
                n.notice('Updating ' + detail + ' value.');

                return new Promise(function(resolve, reject) {
                    $.ajax({
                        url: '/products/details/update',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id: variation.details.id,
                            detail: detail,
                            value: value,
                            sku: variation.sku
                        },
                        success: function() {
                            dispatch({
                                type: "PRODUCT_DETAILS_CHANGE",
                                payload: {
                                    value: value,
                                    detail: detail,
                                    row: variation
                                }
                            });
                            resolve({savedValue: value});
                        },
                        error: function(error) {
                            dispatch({
                                type: "PRODUCT_DETAILS_CHANGE_FAILURE",
                                payload: {error, detail}
                            });
                            reject(new Error(error));
                        }
                    });
                });
            };
        }
    }
})();

export default dimensionActions;


