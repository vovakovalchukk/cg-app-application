define([
    'Product/Storage/Ajax',
    'Product/Filter/Entity',
    'Product/Components/ProductList/Config/constants'
], function(
    AjaxHandler,
    ProductFilter,
    constants
) {
    "use strict";
    
    let actionCreators = (function() {
        return {
            saveDetail: (variation, detail, value) => {
                return async function(dispatch, getState) {
                    console.log('in saveDetail AC variation: ', {
                        variation, detail, value
                    });
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
                                n.success('Successfully updated ' + detail + '.');
                                window.triggerEvent('dimension-' + variation.sku, {
                                    'value': value,
                                    'dimension': detail
                                });
                                dispatch({
                                    type: "PRODUCT_DETAILS_CHANGE",
                                    payload: {
                                        value: value,
                                        detail: detail,
                                        row: variation
                                    }
                                });
                                resolve({savedValue: value});
                            }.bind(this),
                            error: function(error) {
                                n.showErrorNotification(error, "There was an error when attempting to update the " + detail + ".");
                                reject(new Error(error));
                            }
                        });
                    });
                };
            }
        };
    })();
    
    return actionCreators;
});