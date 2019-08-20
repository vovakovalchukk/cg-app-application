"use strict";

let detailActions = (function() {
    return {
        changeDetailValue: (productId, detail, newValue, additional) => {
            return async function(dispatch, getState) {
                let currentDetailsFromProductState = getState.customGetters.getDetailsFromProductState(productId, additional);
                dispatch({
                    type: "DETAIL_VALUE_CHANGE",
                    payload: Object.assign({}, {
                        productId,
                        detail,
                        newValue,
                        currentDetailsFromProductState
                    }, additional || {})
                });
            }
        },
        setIsEditing: (productId, detail, setToBoolean, additional) => {
            return {
                type: "IS_EDITING_SET",
                payload: Object.assign({}, {
                    productId,
                    detail,
                    setToBoolean
                }, additional || {})
            }
        },
        saveDetail: (row, detail, additional) => {
            return async function(dispatch, getState) {
                if (row === null) {
                    return;
                }
                let state = getState();
                let value;
                if (additional && additional.accountId) {
                    value = lookup(state.detail[detail].byAccountId, [additional.accountId, row.id, 'valueEdited']);
                } else {
                    value = lookup(state.detail[detail].byProductId, [row.id, 'valueEdited'])
                }
                n.notice('Updating ' + detail + ' value.');
                let response = await setDetail(row, detail, value, additional);
                if (response.exception) {
                    return dispatch({
                        type: "PRODUCT_DETAILS_CHANGE_FAILURE",
                        payload: response
                    });
                }
                return dispatch({
                    type: "PRODUCT_DETAILS_CHANGE_SUCCESS",
                    payload: Object.assign({}, {
                        value,
                        detail,
                        row
                    }, additional || {})
                });
            }
        },
        cancelInput: (row, detail, additional) => {
            if (row === null) {
                return;
            }
            return ({
                type: "DETAIL_CANCEL_INPUT",
                payload: Object.assign({}, {
                    detail,
                    row
                }, additional || {})
            });
        }
    }
})();

export default detailActions;

function lookup(prop, ids) {
    ids.forEach(id => {
        if (!(id in prop)) {
            prop[id] = {};
        }
        prop = prop[id];
    });

    return prop;
}

async function setDetail(variation, detail, value, additional) {
    return $.ajax({
        url: '/products/details/update',
        type: 'POST',
        dataType: 'json',
        data: Object.assign({}, {
            id: variation.details.id,
            productId: variation.id,
            detail: detail,
            value: value,
            sku: variation.sku
        }, additional || {}),
        success: function(response) {
            return response;
        },
        error: function(error) {
            return error;
        }
    });
}