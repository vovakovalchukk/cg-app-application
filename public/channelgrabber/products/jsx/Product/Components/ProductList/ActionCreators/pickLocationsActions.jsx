"use strict";

let pickLocationsActions = (function() {
    return {
        storePickLocationNames: (pickLocationNames) => {
            return function (dispatch) {
                dispatch({
                    type: "PICK_LOCATION_SET_NAMES",
                    names: pickLocationNames
                });
            };
        },
        storePickLocationValues: (pickLocationValues) => {
            return function (dispatch) {
                dispatch({
                    type: "PICK_LOCATION_SET_VALUES",
                    values: pickLocationValues
                });
            };
        },
        togglePickLocationsSelect: (productId, level) => {
            return function (dispatch) {
                dispatch({
                    type: "PICK_LOCATION_TOGGLE_SELECT",
                    selected: {productId, level}
                });
            };
        },
        selectPickLocation: (productId, level, value) => {
            return async function (dispatch, getState) {
                n.notice('Assigning picking location to product');
                try {
                    dispatch({
                        type: "PICK_LOCATION_SET_PRODUCT_SUCCESS",
                        productId,
                        productPickLocations: await setProductPickLocationValue(
                            getState(),
                            getState.customGetters,
                            productId,
                            level,
                            value
                        ),
                        level,
                        value
                    });
                } catch (err) {
                    dispatch({
                        type: "PICK_LOCATION_SET_PRODUCT_FAILURE",
                        err
                    });
                }
            };
        }
    }
})();

export default pickLocationsActions;

function setProductPickLocationValue(state, stateHelpers, productId, level, value) {
    let productPickLocations = getProductPickLocations(state, stateHelpers, productId);
    productPickLocations[level] = value;
    return $.ajax({
        url: '/products/pickLocation',
        type: 'POST',
        dataType: 'json',
        data: {productId, productPickLocations}
    }).then(() => {
        return productPickLocations;
    });
}

function getProductPickLocations(state, stateHelpers, productId) {
    if (state.pickLocations.byProductId.hasOwnProperty(productId)) {
        return Object.assign({}, state.pickLocations.byProductId[productId]);
    }

    let product = stateHelpers.getProductById(productId);
    if (product && product.hasOwnProperty('pickingLocations')) {
        return Object.assign({}, product.pickingLocations);
    }

    return {};
}