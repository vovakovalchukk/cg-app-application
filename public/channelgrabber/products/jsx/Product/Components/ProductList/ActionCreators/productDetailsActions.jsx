"use strict";

let actionCreators = (function() {
    return {
        saveDetail: (variation, detail, value) => {
            return async function(dispatch, getState) {
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
    };
})();

export default actionCreators;